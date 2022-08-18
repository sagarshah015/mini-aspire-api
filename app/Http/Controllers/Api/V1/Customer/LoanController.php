<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserLoan;
use App\Models\LoanSchedule;
use App\Models\LoanPayment;

class LoanController extends Controller
{
    private $loanPaymentFrequency = 0;
    private $maxLoanAmount = 0;
    private $maxLoanTerms = 0;

    public function __construct(){
        // set private variables for custom variable for loan module
        $this->loanPaymentFrequency = \Config::get('custom_vars.loan_payment_frequency');
        $this->maxLoanAmount = \Config::get('custom_vars.max_loan_amount');
        $this->maxLoanTerms = \Config::get('custom_vars.max_loan_terms');
    }

     /*
        use : Api for loan create
        Request : name,amount,terms as params
        Return : status of loan create
    */
    function loanCreate(Request $request){
    	$validator = Validator::make($request->all(), [
            'name' => 'required|max:30', 
            'amount' => 'required|numeric|min:1|max:'.$this->maxLoanAmount,
            'terms' =>  'required|numeric|min:1|max:'.$this->maxLoanTerms
        ]);
        
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->formatResponse(false,$validator->messages(),$this->validationErrorMessage,422);
        }
        try {
            // All transaction in DB will be rollback if getting some error
            \DB::beginTransaction();
            $userLoanData = array_merge($request->all(), ['userId' => \Auth::user()->id,'dueAmount'=>$request->amount]);
            $userLoan = UserLoan::create($userLoanData);
            if($userLoan){
            	if($this->saveLoanSchedule($userLoan->id,$request)){
                    // if all things works file than commit changes to DB
                    \DB::commit();
                    return $this->formatResponse(true,$userLoan->toArray(),'Loan Created successfully!');
                }
            }
            // thrown a exception if some error occure
            throw new \Exception();            
         }  catch (\Exception $e) {
            \DB::rollBack();
            return $this->formatResponse(false,[],$this->exceptionErrorMessage,400);
        }

    }

    /*
        Use : save loan schedule as per terms
        Return boolean 
    */
    private function saveLoanSchedule($userLoanId,$request){
    	$terms = $request->terms;
        $repaymentAmount = round($request->amount/$terms,4);
        $userPaymentSchedule = [];
        $sumPayments = 0;
        $scheduleDate = date('Y-m-d');
        
        for($i=1;$i<=$terms;$i++){
            // last payment schedule term should be adjust as per remaining amount
        	if($terms > 1 && $i==$terms){
        		$repaymentAmount = round($request->amount -	$sumPayments,4);
        	}
        	$scheduleDate = $this->getScheduleDate($scheduleDate);
        	$userPaymentSchedule[] = [
        			'userLoanId'=>$userLoanId,
        			'amount'=>$repaymentAmount,
        			'term'=>$i,
        			'scheduleDate'=>$scheduleDate,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
        		];
        	$sumPayments+=$repaymentAmount;
        }
        // create loan schedule term amount according to total amount/terms
        if(LoanSchedule::insert($userPaymentSchedule)){
            return true;
        }
        return false;
    }

    // get schedule date according to the frequency
    private function getScheduleDate($date){
    	return date("Y-m-d",strtotime("+".$this->loanPaymentFrequency. "day", strtotime($date)));
    }

    /*
        use : Api for loan repayment
        Request : userLoanId and amount as param
        Return : status of repayment
    */
    function loanRepayment(UserLoan $userLoan,Request $request){
    	$validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1' // validation
        ]);        
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->formatResponse(false,$validator->messages(),$this->validationErrorMessage);
        }
        // check that only loan owner/user can do payment
        if($userLoan->userId!=\Auth::user()->id){
            return $this->formatResponse(false,[],$this->exceptionErrorMessage);
        }

        $loanId = $request->loanId;
        // get all schedule term loans which are pending
        $userLoan = $userLoan->load(['loan_schedules'=>function($query){
            return $query->where('isPaid',0);
        }]);

        $amount = floatval($request->amount);
        // validate loan status and amount
        $errorMessage = $this->validateLoan($userLoan,$amount);
        if(isset($errorMessage['status']) && !$errorMessage['status']){
            return $this->formatResponse(false,[],$errorMessage['message']);
        }
        // check amount with schedule amount
        $scheduleAmount = $this->checkAndGetScheduleAmount($userLoan,$amount);
        if(!$scheduleAmount){
            return $this->formatResponse(false,[],'Amount should not be less than schedule/due amount');
        }
        try {
            \DB::beginTransaction();
            if($this->savePaymentDetails($userLoan,$amount,$scheduleAmount)){
                \DB::commit();
                return $this->formatResponse(true,[],'Loan Repayment successfully!');
            }
            throw new \Exception();            
        }  catch (\Exception $e) {
            \DB::rollBack();
            return $this->formatResponse(false,[],$this->exceptionErrorMessage,400);
        }
    }

    /*
        Use : Save payment details 
        Return a boolean as per condition
    */

    private function savePaymentDetails($userLoan,$amount,$scheduleAmount){
        $paymentTerms = floor($amount/$scheduleAmount);
        
        $loanScheduleIds = [];
        $i=1;
        $lastPaymentTerm = false;
        foreach ($userLoan->loan_schedules as $loanSchedule) {
            $loanScheduleIds[] = $loanSchedule->id;
            // if schedule term is equal to total term of loan then lastPaymentTerm variable sets true  
            if($loanSchedule->term == $userLoan->terms){
                $lastPaymentTerm = true;
            }              
            if($i==$paymentTerms){
                break;
            }
            $i++;
        }
        // update loan_schedules table with status paid
        // if user paid for 2 terms amount in single go, then update isPaid status for 2 terms
        if(LoanSchedule::whereIn('id',$loanScheduleIds)->update(['isPaid'=>1])){
            if($this->saveLoanPayments($userLoan->id,$amount)){
                // if last payment term then dueAmount set as 0
                if($lastPaymentTerm){
                    $userLoan->dueAmount = 0;
                    $userLoan->status = 2;
                }else{
                    // deduct requested amount from dueAmount
                    $userLoan->dueAmount-=$amount;
                }
                if($userLoan->save()){
                    return true;
                }
            }            
        }
        return false;
    }

    /*
        Use : check amount with schedule amount
        Return schedule amount if valid data otherwise return false
    */
    private function checkAndGetScheduleAmount($userLoan,$amount){
        if(isset($userLoan->loan_schedules[0]) && !empty($userLoan->loan_schedules[0])){
            $scheduleAmount = $userLoan->loan_schedules[0]->amount;
            if($amount < $scheduleAmount && $amount < $userLoan->dueAmount){
                return false;
            }
            return $scheduleAmount;
        }
    }

    /*
        Use : validate loan status
        Return a key value array of status and message
    */
    private function validateLoan($userLoan,$amount){
         $message = '';
         $status = true;
        if($userLoan->status==0){
            $status = false;
            $message = 'Admin has not approved loan yet';
        }else if($userLoan->status==2){
            $status = false;
            $message = 'you have already paid this loan';
        }else if($userLoan->dueAmount < $amount){
            $status = false;
            $message = 'Amount should not be greater than due amount('.$userLoan->dueAmount. ' '.config("custom_vars.currency").')';
        }
        return ['status'=>$status,'message'=>$message];
    }

    /*
        Use : save loan payments
        Return boolean 
    */
    private function saveLoanPayments($userLoanId,$amount){
        if(LoanPayment::create(['userLoanId'=>$userLoanId,'amount'=>$amount])){
            return true;
        }
        return false;
    }
    /*
        Use : return all loan of user
        Return a key value array 
        get loans array from loans key in main array
    */
    function viewLoans(){
        $userLoans = User::whereId(\Auth::user()->id)->with(['loans'])->get()->toArray();
        return $this->formatResponse(false,$userLoans,'Customer Loans');
    }
}
