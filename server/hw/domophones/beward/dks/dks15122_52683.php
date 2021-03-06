<?php

    namespace hw\domophones {

        require_once 'dks15122.php';
        require_once 'separated_rfids.php';

        class dks15122_52683 extends dks15122 {

            use separated_rfids;

            protected $cms_models = [
                'KKM-100S2' => 0,
                'KKM-105' => 1,
                'KAD2501' => 2,
                'KKM-108' => 3,
            ];

            function configure_user_account(string $password) {
                parent::configure_user_account($password);

                $this->api_call('cgi-bin/pwdgrp_cgi', [
                    'action' => 'update',
                    'username' => 'user1',
                    'password' => $password,
                ]);
            }

        }

    }
