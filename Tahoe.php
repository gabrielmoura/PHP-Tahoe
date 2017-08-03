<?php
/**
 * Date: 8/3/17
 * Time: 1:27 AM
 */

namespace App\external;


class Tahoe
{

    /**
     * /param url or file
     * @return mixed
     */
    public function getFile($name)
    {
        return $this->endpoint($name, true);
    }


    /**
     * @param $dirurl
     * @return array
     */
    public function list_files($dirurl)
    {
        return array_keys($this->toArray($this->dirnode($dirurl)->children));
    }

    /**
     * @param $dirurl
     * @return array
     */
    public function array_files($dirurl)
    {
        return $this->toArray($this->dirnode($dirurl)->children);
    }


    /**
     * @param $dirurl
     * @return bool
     */
    public function is_dirnode($dirurl)
    {
        if ($this->endpoint($dirurl)[0] == 'dirnode') :
            return true;
        else :
            return false;
        endif;
    }

    /**
     * @param $url
     * @return bool
     */
    public function is_filenode($url)
    {
        if ($this->endpoint($url)[0] == 'filenode') :
            return true;
        else :
            return false;
        endif;
    }

    /**
     * @param $dirURL
     * @param $contentFile
     * @param null $filename
     * @return mixed
     */
    public function mkfile($dirURL, $contentFile, $filename = null)
    {
        return $this->url_post_contents(
            'localhost:3456/uri/' . $dirURL . $filename,
            [
                'file' => $contentFile,
                'format' => 'chk',
                't' => 'upload'
            ]
        );
    }

    /**
     * @param $dirURL
     * @param $title
     * @return mixed
     */
    public function mkdir($dirURL, $title)
    {
        $mkdir = $this->url_post_contents(
            'localhost:3456/uri/' . $dirURL,
            [
                't' => 'mkdir',
                'format' => 'mdmf',
                'when_done' => '.',
                'name' => $title,
            ]
        );
        return $mkdir;
    }

    /**
     * @return mixed
     */
    public function mkdirnode()
    {
        $dirnode = $this->url_post_contents('localhost:3456/uri',
            [
                'format' => 'mdmf',
                't' => 'mkdir',
                'redirect_to_result' => true,
            ]
        );
        return $dirnode;
    }

    // PROTECTED

    /**
     * @param $dirurl
     * @return mixed
     */
    protected function dirnode($dirurl)
    {
        $curl = $this->endpoint($dirurl)[1];
        return $curl;
    }


    //PRIVATE

    /**
     * @param $obj
     * @return array
     */
    private function toArray($obj)
    {
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = self::toArray($val);
            }
        } else {
            $new = $obj;
        }
        return $new;
    }

    /**
     * @param $command
     * @param null $export
     * @return mixed
     */
    private function endpoint($command, $export = null)
    {
        if ($export == null) :
            return json_decode($this->url_get_contents('localhost:3456/uri/' . $command . '?t=json'));
        else :
            return $this->url_get_contents('localhost:3456/uri/' . $command);
        endif;
    }

    /**
     * @param $Url
     * @return mixed
     */
    private function url_get_contents($Url)
    {
        if (!function_exists('curl_init')) {
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * @param $url
     * @param array $data
     * @return mixed
     */
    private function url_post_contents($url, array $data)
    {
        foreach ($data as $key => $value) {
            $fields_string = $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
