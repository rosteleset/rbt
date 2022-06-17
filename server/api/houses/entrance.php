<?php

    /**
     * houses api
     */

    namespace api\houses
    {

        use api\api;

        /**
         * entrance method
         */

        class entrance extends api
        {


            public static function POST($params)
            {
                $houses = loadBackend("houses");

                if (@$params["entranceId"]) {
                    $success = $houses->addEntrance($params["houseId"], $params["entranceId"], $params["prefix"]);

                    return api::ANSWER($success, ($success !== false)?false:"notAcceptable");
                } else {
                    $entranceId = $houses->createEntrance($params["houseId"], $params["entranceType"], $params["entrance"], $params["lat"], $params["lon"], $params["shared"], $params["prefix"], $params["domophoneId"], $params["domophoneOutput"], $params["cmsType"], $params["cameraId"]);

                    return api::ANSWER($entranceId, ($entranceId !== false)?"entranceId":"notAcceptable");
                }
            }

            public static function PUT($params)
            {
                $houses = loadBackend("houses");

                $success = $houses->modifyEntrance($params["_id"], $params["houseId"], $params["entranceType"], $params["entrance"], $params["lat"], $params["lon"], $params["shared"], $params["prefix"], $params["domophoneId"], $params["domophoneOutput"], $params["cmsType"], $params["cameraId"]);

                return api::ANSWER($success, ($success !== false)?false:"notAcceptable");
            }

            public static function DELETE($params)
            {
                $houses = loadBackend("houses");

                if (@$params["houseId"]) {
                    $success = $houses->deleteEntrance($params["_id"], $params["houseId"]);
                } else {
                    $success = $houses->destroyEntrance($params["_id"]);
                }

                return api::ANSWER($success, ($success !== false)?false:"notAcceptable");
            }

            public static function index()
            {
                return [
                    "POST" => "#same(houses,house,PUT)",
                    "PUT" => "#same(houses,house,PUT)",
                    "DELETE" => "#same(houses,house,PUT)",
                ];
            }
        }
    }