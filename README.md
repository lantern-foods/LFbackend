## Getting Started
- Clone the project on your local machine
- cd to the project folder and open in terminal
- Run *composer update* command to install the necessary packages
- Create your development database
- cp .env.example .env
- Add database name and database user credentials to .env
- Run *php artisan migrate* command
- Run *php artisan db:seed* command

## Passport setup
To generate Client ID and Client Secret: <br>
- Run *php artisan passport:client --personal* command <br>
- Thereafter copy the values of ***Client ID*** and ***Client Secret*** from the terminal and update the following in .env file:
``
PASSPORT_PERSONAL_ACCESS_CLIENT_ID="client-id-value"
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET="unhashed-client-secret-value"
``