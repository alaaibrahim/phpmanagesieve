<?php
/**
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * phpmanagesieve/Net/TimeoutSocket.php Created on 08-01-09
 *
 * @author Ala'a A. Ibrahim <ala.ibrahim@gmail.com>
 * @package phpmanagesieve
 * @subpackage Net
 */
/**
 * [Class Description Here ...]
 * 
 */
class phpmanagesieve_Net_TimeoutSocket extends phpmanagesieve_Net_Socket implements phpmanagesieve_Net_ISocket {
    
    // Methods
    /**
     * Constructor
     *
     * @param string $host The host of the server to connect to.
     * @param integer $port The port of the connection.
     * @param integer[optional] $timeout Timeout of the connection.
     * @param float[optional] $blockingTimeout Timeout while waiting for a response.
     */
    public function __construct($host, $port, $timeout = 30, $blockingTimeout = 2.0) {
        parent::__construct($host, $port, $timeout);
        $this->setTimeout($blockingTimeout);

    }

    public function __destruct() {
    }

    /**
     * gets
     * 
     * Get a string from the socket
     * @param integer[optional] $length
     * @return string
     */
    public function gets($length = null) {
        $returnValue = parent::gets($length);
        $info = stream_get_meta_data($this->socket);
        if ($info['timed_out']) {
            throw new phpmanagesieve_Net_Socket_Exception(
                'Socket Timeout ' ,
                phpmanagesieve_Net_Socket_Exception::TIMEOUT
            );
        }
        return $returnValue;
    }

    /**
     * setTimeout
     * @param float $sec amount of timeout in seconds
     */
    public function setTimeout ($sec) {
        $blockingTimeOutSec = floor($sec);
        $usec = ($sec - $blockingTimeOutSec) * 1000;
        stream_set_timeout($this->socket, $blockingTimeOutSec, $usec);
    }
}
