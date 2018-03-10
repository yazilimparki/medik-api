<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width">
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <style type="text/css">
            * {
                margin: 0;
                box-sizing: border-box;
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                font-size: 14px;
            }

            body {
                -webkit-font-smoothing: antialiased;
                -webkit-text-size-adjust: none;
                width: 100% !important;
                height: 100%;
                line-height: 1.6em;
                background-color: #f6f6f6;
            }

            img {
                max-width: 100%;
            }

            table td {
                vertical-align: top;
            }

            .body-wrap {
                background-color: #f6f6f6;
                width: 100%;
            }

            .container {
                max-width: 600px !important;
                margin: 0 auto !important;
                display: block !important;
                clear: both !important;
            }

            .content {
                max-width: 600px;
                margin: 0 auto;
                display: block;
                padding: 20px;
            }

            .main {
                background-color: #ffffff;
                border: 1px solid #e9e9e9;
                border-radius: 3px;
                -webkit-border-radius: 3px;
                -moz-border-radius: 3px;
            }

            .content-wrap {
                padding: 20px;
            }

            .content-block {
                padding: 0 0 20px;
            }

            .footer {
                width: 100%;
                clear: both;
                color: #999999;
                padding: 20px;
            }

            .footer td, .footer a {
                color: #999999;
                font-size: 12px;
            }

            p {
                margin-bottom: 10px;
                font-weight: normal;
            }

            a {
                color: #348eda;
                text-decoration: underline;
            }

            .btn-primary {
                text-decoration: none;
                color: #ffffff;
                background-color: #348eda;
                border: solid #348eda;
                border-width: 10px 20px;
                line-height: 2em;
                font-weight: bold;
                text-align: center;
                cursor: pointer;
                display: inline-block;
                border-radius: 5px;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
            }

            .text-center {
                text-align: center;
            }

            @media only screen and (max-width: 640px) {
                body {
                    padding: 0 !important;
                }

                .container {
                    padding: 0 !important;
                    width: 100% !important;
                }

                .content {
                    padding: 0 !important;
                }

                .content-wrap {
                    padding: 10px !important;
                }
            }
        </style>
    </head>
    <body>
    <table class="body-wrap" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td></td>
            <td class="container" width="600">
                <div class="content">
                    <table class="main" width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="content-wrap">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="content-block">
                                            <?php
                                            $this->beginBody();

                                            echo $content;

                                            $this->endBody();
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="content-block text-center"><a href="http://example.com">Example</a></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    </body>
    </html>
<?php $this->endPage() ?>
