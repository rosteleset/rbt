<?php

    /**
     * backends attachments namespace
     */

    namespace backends\attachments {

        use backends\backend;

        /**
         * local storage attachments class
         */

        abstract class attachments extends backend {

            /**
             * add file to storage
             *
             * @param string $meta
             * @param string $fileContent
             * @return string uuid
             */

            abstract public function addFile($meta, $fileContent);

            /**
             * add file to storage
             *
             * @param string $meta
             * @return string uuid
             */

            abstract public function uploadFile($meta);

            /**
             * get file from storage
             *
             * @param $uuid
             * @return object file, filename, metadata
             */

            abstract public function getFile($uuid);

            /**
             * delete file
             *
             * @param $uuid
             * @return boolean
             */

            abstract public function deleteFile($uuid);
        }
    }
