<?php
/**
 * Medik (r) Photo Sharing Platform for Health Professionals (http://medik.com)
 * Copyright (c) Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti. (http://yazilimparki.com.tr)
 *
 * Licensed under The MIT License (https://opensource.org/licenses/mit-license.php)
 * For full copyright and license information, please see the LICENSE.txt file.
 * Redistributions of files must retain the above copyright notice.
 *
 * Medik (r) is registered trademark of Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti.
 *
 * @copyright Copyright (c) Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti. (http://yazilimparki.com.tr)
 * @link http://medik.com Medik (r) Photo Sharing Platform for Health Professionals
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */

namespace api\rest;

class Serializer extends \yii\rest\Serializer
{
    /**
     * @inheritdoc
     */
    protected function serializePagination($pagination)
    {
        $envelope = parent::serializePagination($pagination);

        $envelope[$this->metaEnvelope] = [
            'total_count' => $envelope[$this->metaEnvelope]['totalCount'],
            'page_count' => $envelope[$this->metaEnvelope]['pageCount'],
            'current_page' => $envelope[$this->metaEnvelope]['currentPage'],
            'per_page' => $envelope[$this->metaEnvelope]['perPage'],
        ];

        unset(
            $envelope[$this->metaEnvelope]['totalCount'],
            $envelope[$this->metaEnvelope]['pageCount'],
            $envelope[$this->metaEnvelope]['currentPage'],
            $envelope[$this->metaEnvelope]['perPage']
        );

        return $envelope;
    }
}
