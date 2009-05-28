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
 * phpmanagesieve/Net/Socket.php Created on 08-01-09
 *
 * @author Ala'a A. Ibrahim <ala.ibrahim@gmail.com>
 * @package phpmanagesieve
 * @subpackage Net
 */
/**
 * A wrapper to the sockets interface in PHP.
 *
 * This wapper is reponsible for sockets, using the fsockopen function 
 */
class phpmanagesieve_Net_Socket implements phpmanagesieve_Net_ISocket{
    // Variables
    /**
     * The actual place where we save our socket Resource
     * 
     * @var resource
     * @access protected
     */
    protected $socket;

    // Methods

    /**
     * Constructor
     *
     * @param string $host The host of the server to connect to.
     * @param integer $port The port of the connection.
     * @param integer[optional] $timeout Timeout of the connection.
     */
    public function __construct($host, $port, $timeout = 30) {
        $errno = 0;
        $errstr = '';
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$this->socket) {
            throw New phpmanagesieve_Net_Socket_Exception('Couldn\'t Connect:' . $errstr, $errno);
        }
    }

    public function __destruct() {
        if ($this->socket) {
             @fclose($this->socket);
        }
    }

    /**
     * gets
     * 
     * Get a string from the socket
     * @param integer[optional] $length
     * @return string
     */
    public function gets($length = null) {
        return isset($length) ?
            @fgets($this->socket, $length):@fgets($this->socket);
    }

    /**
     * puts
     *
     * Put a string into a socket
     * @param string $string string to write
     * @param integer[optional] $length If the length argument is given, writing will stop after length bytes have been written or the end of string is reached, whichever comes first.
     * @return integer number of bytes written.
     */
    public function puts($string, $length = null) {
        return isset($length) ?
            @fputs($this->socket, $string, $length):
            @fputs($this->socket, $string);
    }

    /**
     * eof
     *
     * Tests for end-of-file
     * @return bool
     */
    public function eof() {
        return @feof($this->socket);
    }
}
