<?php
/**************************************************************
Author: Jahangir Mohammed
mail: info@jahmoh.co.uk
github: Jahmoh82

Comment the option curl_setopt($x, CURLOPT_TCP_FASTOPEN, 1);
if your libcurl version is not equal or grater than 7.49.0
and/or if a rest api do not support this option.
Documentation:
https://curl.haxx.se/libcurl/c/symbols-in-versions.html
http://php.net/manual/en/ref.curl.php
http://php.net/manual/it/function.curl-setopt.php
http://php.net/manual/it/function.curl-multi-setopt.php
***************************************************************/

class MultipleCurlArray
{
    private function set_option($x, $y)
    {
        curl_setopt($x, CURLOPT_URL, $y);
        curl_setopt($x, CURLOPT_TIMEOUT, 20);
        curl_setopt($x, CURLOPT_HEADER, 0);
        curl_setopt($x, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($x, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($x, CURLOPT_TCP_FASTOPEN, 1);
        curl_setopt($x, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($x, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($x, CURLOPT_SSL_VERIFYHOST, 0);
    }

    public function run_multi_curl($urlarray)
    {
        $n = count($urlarray);
        $ch[0] = curl_init();
        $this->set_option($ch[0], $urlarray[0]);

        $mh = curl_multi_init();
        curl_multi_setopt($mh, CURLMOPT_PIPELINING, 1);
        curl_multi_setopt($mh, CURLMOPT_MAXCONNECTS, $n);
        curl_multi_add_handle($mh, $ch[0]);
        foreach ($urlarray as $k => $urlarrayvalue) {
            if ($k !== 0) {
                $ch[$k] = curl_init();
                $this->set_option($ch[$k], $urlarrayvalue);
                curl_multi_add_handle($mh, $ch[$k]);
            }
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        $q = 0;
        do {
            $results[$q] = curl_multi_getcontent($ch[$q]);
            curl_multi_remove_handle($mh, $ch[$q]);
            $q++;
        } while ($q < $n);
        return $results;
    }

    public function multi_curl_restapi($urlarray, $postfield, $headers)
    {
        $n = count($urlarray);
        $ch[0] = curl_init();
        $this->set_option($ch[0], $urlarray[0]);
        curl_setopt_array($ch[0], [
            CURLOPT_POSTFIELDS => $postfield[0],
            CURLOPT_HTTPHEADER => $headers[0]
        ]);

        $mh = curl_multi_init();
        curl_multi_setopt($mh, CURLMOPT_PIPELINING, 1);
        curl_multi_setopt($mh, CURLMOPT_MAXCONNECTS, $n);
        curl_multi_add_handle($mh, $ch[0]);
        foreach ($urlarray as $k => $urlarrayvalue) {
            if ($k !== 0) {
                $ch[$k] = curl_init();
                $this->set_option($ch[$k], $urlarrayvalue);
                curl_setopt_array($ch[$k], [
                    CURLOPT_POSTFIELDS => $postfield[$k],
                    CURLOPT_HTTPHEADER => $headers[$k]]);
                curl_multi_add_handle($mh, $ch[$k]);
            }
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        $q = 0;
        do {
            $results[$q] = curl_multi_getcontent($ch[$q]);
            curl_multi_remove_handle($mh, $ch[$q]);
            $q++;
        } while ($q < $n);

        return $results;
    }
}
?> 