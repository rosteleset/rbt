<?php

    class PDO_EXT extends PDO {

        function trimParams($map) {
            $remap = [];

            foreach ($map as $key => $value) {
                if (is_null($value)) {
                    $remap[$key] = $value;
                } else {
                    $remap[$key] = trim($value);
                }
            }

            return $remap;
        }

        function insert($query, $params = []) {
            error_log($query);
            try {
                $sth = $this->prepare($query);
                if ($sth->execute($this->trimParams($params))) {
                    return $this->lastInsertId();
                } else {
                    return false;
                }
            } catch (\PDOException $e) {
                setLastError($e->errorInfo[2]?:$e->getMessage());
                error_log(print_r($e, true));
                return false;
            } catch (\Exception $e) {
                setLastError($e->getMessage());
                error_log(print_r($e, true));
                return false;
            }
        }

        function modify($query, $params = []) {
            error_log($query);
            try {
                $sth = $this->prepare($query);
                if ($sth->execute($this->trimParams($params))) {
                    return $sth->rowCount();
                } else {
                    return false;
                }
            } catch (\PDOException $e) {
                setLastError($e->errorInfo[2]?:$e->getMessage());
                error_log(print_r($e, true));
                return false;
            } catch (\Exception $e) {
                setLastError($e->getMessage());
                error_log(print_r($e, true));
                return false;
            }
        }

        function modifyEx($query, $map, $params) {
            error_log($query);
            $mod = false;
            try {
                foreach ($map as $db => $param) {
                    if (array_key_exists($param, $params)) {
                        $sth = $this->prepare(sprintf($query, $db, $db));
                        if ($sth->execute($this->trimParams([
                            $db => $params[$param],
                        ]))) {
                            if ($sth->rowCount()) {
                                $mod = true;
                            }
                        }
                    }
                }
                return $mod;
            } catch (\PDOException $e) {
                setLastError($e->errorInfo[2]?:$e->getMessage());
                error_log(print_r($e, true));
                return false;
            } catch (\Exception $e) {
                setLastError($e->getMessage());
                error_log(print_r($e, true));
                return false;
            }
        }

        function get($query, $params = [], $map = [], $options = []) {
            error_log($query);
            try {
                if ($params) {
                    $sth = $this->prepare($query);
                    if ($sth->execute($params)) {
                        $a = $sth->fetchAll(\PDO::FETCH_ASSOC);
                    } else {
                        return false;
                    }
                } else {
                    $a = $this->query($query, \PDO::FETCH_ASSOC)->fetchAll();
                }

                $r = [];

                if ($map) {
                    foreach ($a as $f) {
                        $x = [];
                        foreach ($map as $k => $l) {
                            $x[$l] = $f[$k];
                        }
                        $r[] = $x;
                    }
                } else {
                    $r = $a;
                }

                if (in_array("singlify", $options)) {
                    if (count($r) === 1) {
                        return $r[0];
                    } else {
                        return false;
                    }
                }

                if (in_array("fieldlify", $options)) {
                    if (count($r) === 1) {
                        return $r[0][array_key_first($r[0])];
                    } else {
                        return false;
                    }
                }

                return $r;

            } catch (\PDOException $e) {
                setLastError($e->errorInfo[2]?:$e->getMessage());
                error_log(print_r($e, true));
                return false;
            } catch (\Exception $e) {
                setLastError($e->getMessage());
                error_log(print_r($e, true));
                return false;
            }
        }

        function now() {
            return date("Y-m-d H:i:s.000");
        }
    }
