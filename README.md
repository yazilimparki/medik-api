# Medik<sup>&reg;</sup> API Service
[Medik](http://medik.com)<sup>&reg;</sup> is a photo sharing platform for health professionals.

Medik<sup>&reg;</sup> API service source code is written in PHP using [Yii Framework](http://www.yiiframework.com) version 2 (advanced template is used).

*This repository is currently lack of management (administration) and frontend (public sharing) source codes, tests and migrations.*

Check `common/config` and `api/config` directories for configuration settings.

**To create API documentation:**

Install [apiDoc](http://apidocjs.com)
```sh
npm install apidoc -g
```

Run those command:
```sh
apidoc -c apidoc.json -i api/controller/ -o output_directory_of_documentation/
```

### Requirements
* PHP >= 5.4
* MySQL database server
* AWS account for S3 object-storage
* Parse account for push notifications
* [apiDoc](http://apidocjs.com) to create API documentation

### Copyright
Copyright &copy; Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti. (http://yazilimparki.com.tr)

### License
Licensed under [The MIT License](https://opensource.org/licenses/mit-license.php).
For full copyright and license information, please see the LICENSE.txt file.
Redistributions of files must retain the source code copyright notices.

### Disclaimer
No responsiblity for any security risks or software bugs whatsoever is accepted by the Yazılım Parkı.

### Legal Notice
Medik<sup>&reg;</sup> is registered trademark of Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti.

