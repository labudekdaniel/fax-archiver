Requirements
- PHP >= 7.4
- Composer for dependency management
- ZBar for QR code scanning (install with: `sudo apt install zbar-tools`)

Install and run
- Clone the repository
- Run `cd fax-archiver`
- Run `composer install`
- Edit config.json if you want
- Copy inputs into input folder
- Run `chmod -R 777 input/ outputs/ logs/`
- Run `php index.php`