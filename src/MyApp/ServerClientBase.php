<?php

namespace MyApp;

abstract class ServerClientBase {

	protected function buildMessage($sequence, $data) {
		$message = array_merge(['seq' => $sequence, 'msg' => $data]);

		return json_encode($message);
	}

	protected function decodeReply($reply) {
		return json_decode($reply,  true);
	}

    protected function multiImplode($array, $glue)
    {
        if (!is_array($array)) {
            return $array;
        }

        $ret = '';
        foreach ($array as $item) {
            if (is_array($item)) {
                $ret .= $this->multiImplode($item, $glue) . $glue;
            } else {
                $ret .= $item . $glue;
            }
        }
        $ret = substr($ret, 0, 0-strlen($glue));

        return $ret;
    }
}