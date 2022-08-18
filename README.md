Project Configuration
Php version : PHP 8.1.6
Laravel framework version : 8.83.23

1) Download/clone project from git repository
    git clone https://github.com/sagarshah015/mini-aspire-api.git
2) Go to the project directory
3) Inside project -> open Command prompt
4) Run command "composer update" - that will download project dependency/packages
5) Open any mysql editor and create a new database :
	// -------------------------------------------------
		CREATE DATABASE `aspire-git` /*!40100 DEFAULT CHARACTER SET latin1 */;
	// --------------------------------------------------------------
6) Change .env file as per your local database configuration
7) After composer update run following command for table create, user and admin dummy entry
        php artisan migrate    

        php artisan db:seed --class=AdminSeeder
        php artisan db:seed --class=UserSeeder

8) Open postman for check all apis
    Add following headers for all api
        api-key=491d4038-fb78-4c4f-b7eb-adb8ba0a642f
        secret-key=4IY6an94AKW2TLoDXJBD
    
    Add following header for after login apis which need authnetication to use that api
        Authorization = Bearer TOKEN_FROM_LOGIN_API

    For postman collection, refer following link for a reference
    https://documenter.getpostman.com/view/7492196/VUqmvKDk#b68808f6-94fd-4b55-a324-4372894f7429

9) Login apis : find token in headers

Note: Please refer postman collection for more details