<?php

    namespace hw\domophones {

        require_once __DIR__ . '/../hw.php';

        use Exception;
        use hw\hw;

        abstract class domophones extends hw {

            public $user;
            public $pass;

            protected $def_pass;

            /**
             * @throws Exception если панель недоступна
             */
            public function __construct(string $url, string $pass, bool $first_time = false) {
                parent::__construct($url);

                if ($first_time) {
                    $this->pass = $this->def_pass;
                    $this->set_admin_password($pass);
                    $this->write_config();
                }

                $this->pass = $pass;

                if (!$this->ping()) {
                    throw new Exception("$this->url is unavailable");
                }
            }

            public function __destruct() {
                $this->write_config();
            }

            public function ping(): bool {
                $errno = false;
                $errstr = '';
                $socket = @explode('://', $this->url)[1];
                $fp = @stream_socket_client($socket, $errno, $errstr, 1);

                if ($fp) {
                    fclose($fp);

                    if (@$this->get_sysinfo()['DeviceID']) {
                        return true;
                    }

                    return false;
                }

                return false;
            }

            /** Добавить RFID-ключ */
            abstract public function add_rfid(string $code);

            /** Сделать снимок */
            abstract public function camshot(): string;

            /** Очистка квартиры */
            abstract public function clear_apartment(int $apartment = -1);

            /** Удалить RFID-ключ */
            abstract public function clear_rfid(string $code = '');

            /** Настроить параметры квартиры */
            abstract public function configure_apartment(
                int $apartment,
                bool $private_code_enabled,
                bool $cms_handset_enabled,
                array $sip_numbers = [],
                int $private_code = 0
            );

            /** Настроить ККМ адресацию для квартиры ("нормальное" заполнение) */
            abstract public function configure_cms(int $apartment, int $offset);

            /** Настроить ККМ адресацию для квартиры ("перемапленное" заполнение) */
            abstract public function configure_cms_raw(int $index, int $dozens, int $units, int $apartment);

            /** Настроить режим калитки */
            abstract public function configure_gate(array $links);

            /** Настроить параметры обнаружения движения */
            abstract public function configure_md(
                int $sensitivity,
                int $left = 0,
                int $top = 0,
                int $width = 0,
                int $height = 0
            );

            /** Настроить NTP */
            abstract public function configure_ntp(string $server, int $port, string $timezone);

            /** Настроить SIP */
            abstract public function configure_sip(
                string $login,
                string $password,
                string $server,
                int $port = 5060,
                bool $nat = false
            );

            /** Настроить remote syslog */
            abstract public function configure_syslog(string $server, int $port);

            /** Настроить аккаунт user */
            abstract public function configure_user_account(string $password);

            /** Настроить видеопоток(-и) */
            abstract public function configure_video_encoding();

            /** Включить/выключить публичный код доступа */
            abstract public function enable_public_code(bool $enabled = true);

            /** Получить уровни аудио */
            abstract public function get_audio_levels(): array;

            /** Получить распределение ККМ */
            abstract public function get_cms_allocation(): array;

            /** Получить уровни ККМ */
            abstract public function get_cms_levels(): array;

            /** Получить список RFID-ключей */
            abstract public function get_rfids(): array;

            /** Получить системную информацию */
            abstract public function get_sysinfo(): array;

            /** Держать двери открытыми */
            abstract public function keep_doors_unlocked(bool $unlocked = true);

            /** Сделать диагностику линии */
            abstract public function line_diag(int $apartment);

            /** Открыть дверь */
            abstract public function open_door(int $door_number = 0);

            /** Задать пароль доступа для admin */
            abstract public function set_admin_password(string $password);

            /** Задать уровни аудио */
            abstract public function set_audio_levels(array $levels);

            /** Задать таймаут вызова (секунд) */
            abstract public function set_call_timeout(int $timeout);

            /** Задать уровни ККМ */
            abstract public function set_cms_levels(array $levels, int $apartment = -1);

            /** Задать модель ККМ */
            abstract public function set_cms_model(string $model = '');

            /** Задать SIP-номер для кнопки вызова консьержа */
            abstract public function set_concierge_number(int $number);

            /** Задать текст панели (дисплей + оверлей) */
            abstract public function set_display_text(string $text = '');

            /** Задать публичный код доступа */
            abstract public function set_public_code(int $code);

            /** Задать DTMF-коды для открытия дверей */
            abstract public function set_relay_dtmf(int $relay_1, int $relay_2, int $relay_3);

            /** Задать SIP-номер для кнопки SOS */
            abstract public function set_sos_number(int $number);

            /** Задать таймаут разговора (секунд) */
            abstract public function set_talk_timeout(int $timeout);

            /** Задать время открытия замка(-ов) (секунд) */
            abstract public function set_unlock_time(int $time);

            /** Задать текст поверх видео */
            abstract public function set_video_overlay(string $title = '');

            /** Задать язык WEB-интерфейса */
            abstract public function set_web_language(string $lang);

            /** Принудительно сохранить настройки */
            abstract public function write_config();

            /** Подготовить панель */
            abstract public function prepare();

            /** Очистить и настроить панель */
            public function clean(
                string $mgmt_server,
                string $sip_username,
                int $sip_port,
                int $syslog_port,
                array $audio_levels = [],
                array $cms_levels = [],
                string $cms_model = '',
                bool $nat = false
            ) {
                $this->keep_doors_unlocked();
                $this->set_unlock_time(5);
                $this->set_concierge_number(9999);
                $this->set_sos_number(112);
                $this->enable_public_code(false);
                $this->set_call_timeout(45);
                $this->set_talk_timeout(90);
                $this->set_web_language('RU');
                $this->set_audio_levels($audio_levels);
                $this->set_cms_levels($cms_levels);
                $this->configure_ntp($mgmt_server, 123, 'GMT+03:00');
                $this->configure_sip($sip_username, $this->pass, $mgmt_server, $sip_port, $nat);
                $this->set_relay_dtmf(1, 2, 3);
                $this->configure_syslog($mgmt_server, $syslog_port);
                $this->clear_rfid();
                $this->clear_apartment();
                $this->set_cms_model($cms_model);
                $this->configure_gate([]);
                $this->configure_video_encoding();
            }

        }

    }
