<?php

    /**
     * "silent" accounting (logging) class, do nothing
     */

    namespace backends\accounting {

        /**
         * "silent" accounting (logging) class
         */

        class none extends accounting {

            /**
             * @param object $params all params passed to api handlers
             * @param integer $code return code
             * @return void
             */

            public function log($params, $code) {
                // do nothing
            }
        }
    }
