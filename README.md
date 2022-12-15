# Okta Lab Manager

This tool is designed to help you with your Okta lab. This is currently available at https://olm.fastlogin.uk to sign up for and use. 

# Set Up

This tool leverages Auth0 users and roles. 

Copy .env.example to **.env** and update the details.

    AUTH0_DOMAIN='https://tenant.eu.auth0.com'
    AUTH0_CLIENT_ID='<<clientid>>'
    AUTH0_CLIENT_SECRET='<<secret>'
    # A long secret value we'll use to encrypt session cookies. This can be generated using `openssl rand -hex 32` from our shell.
    AUTH0_COOKIE_SECRET='<<cookie-secret>>'
    # The base URL of our application.
    AUTH0_BASE_URL='https://service.url'
    # Base Folder for the router
    APP_BASE='/'
    # Title of your environment (for the header)
    LAB_NAME="Okta Lab Manager"

Second - you need to use composer to setup dependancies. 

    $ composer install
    $ mkdir myDatabase
    $ chown <<youruser>>:www-data myDatabase

Nginx Config Example

	   location @rewrite {
	       rewrite ^/(.*)$ /index.php?_url=/$1;
       }
       location / {
	       if (!-e $request_filename){
	        rewrite ^(.*)$ /index.php;
	       }
       }
       # pass PHP scripts to FastCGI server
       location ~ \.php$ {
	       include snippets/fastcgi-php.conf;
	       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
	       fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
       }


# Features

For any feature requests please raise an issue in this repo. 

## Branding Management

You are able to add brands allowing you to quickly swap between different Okta tenant look and feels. 

This also lets you quickly 'reset' branding back to Okta defaults, in case you need to quickly remove any branding you've made. 

There are also curated shared brands allowing more generic looks to be applied as needed. 

## User Management 

This section will list all users in your tenant, and can take a little while load. 
 
 You can quickly see at a glance the users Groups, and also add them to a custom 'Salesforce' group, and to the Admin group we set up to handle who can see admin functions in this tool (with self removal protection!). 

You can quickly see the source of users too, to easily demo multiple profile sources (AD, Okta, Database, etc). 

### Add Users (inc. Random Name Generator)

Allowing you to add users to your lab quickly, generating their name automatically using the https://randomuser.me API. 
