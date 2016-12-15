# php-deprecated
extract deprecated php functions from php-documentation and scan code for it

# Usage

- Download PHP Manual from http://php.net/download-docs.php (Many HTML files tar.gz)
- Extract the tar gz file e.g. /tmp/php-chunked-xhtml/
- Extract the deprecated functions: `php extract.php /tmp/php-chunked-xhtml/`
- You will get `data.json` file in the script directory
- Scan for deprecated functions in your project: `php scan.php 7.0.0 /var/www/project`
