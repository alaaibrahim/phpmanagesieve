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
 * phpmanagesieve/Mail/ManageSieve.php Created on 08-01-09
 *
 * @author Ala'a A. Ibrahim <ala.ibrahim@gmail.com>
 * @package phpmanagesieve
 * @subpackage Mail
 */
/**
 * ManageSieve Protocol Management 
 * 
 */
class phpmanagesieve_Mail_ManageSieve {
    // Constants
    const OK = 0x00;
    const NO = 0x01;
    const ERR = 0x02;

    // Variables
    /**
     * The Socket that we are using to implement the protocol
     *
     * @var phpmanagesieve_Net_ISocket
     * @access protected
     */
    protected $socket;

    /**
     *
     *
     * @var integer
     * @access protected
     */
     protected $lastReturn;

    /**
     * Constructor
     *
     * @param phpmanagesieve_Net_ISocket $socket the socket we are going to use for the connection.
     */
    public function __construct(phpmanagesieve_Net_ISocket $socket) {
        $this->socket = $socket;
        $this->getTillOk();
    }

    public function __destruct() {
        //TODO: TO make a descion weather we should send a logout
        // Command on Exit or Not, let me think it over :)
    }

    /**
     * login
     * Send an Authenticate Command to the socket
     *
     * @param string $user username
     * @param string $pass Password
     * @return bool
     */
    public function login($user,$pass) {
        $authLine = base64_encode("\0$user\0$pass");
        $this->socket->puts('AUTHENTICATE "PLAIN" {'.strlen($authLine)."}\r\n");
        $this->socket->puts($authLine."\r\n");
        $return = $this->getTillOk();
        if (preg_match('/^ERR /',$return)) {
            throw new phpmanagesieve_Mail_ManageSieve_Exception("LOGIN: ".$return);
        } elseif (preg_match('/^OK /', $return)) {
            return true;
        } elseif (preg_match('/^NO /', $return)) {
            return false;
        }
    }
    
    /**
     * ListScripts
     * Sends a Listscripts command, and returns in an array all the
     * scripts that are listed, in the subarray with key "ACTIVE", it
     * puts the active script, and in "NOTACTIVE" the rest of the scripts
     * 
     * @return array
     */
    public function Listscripts() {
        $this->socket->puts('Listscripts'."\r\n");
        $scripts = array("ACTIVE" => array(), "NOTACTIVE" => array());
        while (true) {
            $line = $this->socket->gets();
            if (preg_match('/^(OK|NO|ERR) /',$line)) {
                //TODO: throw an exception on ERR
                break;
            } elseif (preg_match('/^"(.*)" ACTIVE\s*$/i',$line,$name)) {
                $scripts["ACTIVE"][] = $name[1];
            } elseif (preg_match('/^"(.*)$/i',$line,$name)) {
                $scripts["NOTACTIVE"][] = $name[1];
            } else {
                echo "UNKNOWN".PHP_EOL;
            }
        }
        return $scripts;
    }

    /**
     * SetActive
     * Send a set active command to the socket, to set the script defined
     * in the scriptName
     *
     * @param string $scriptName
     */
    public function SetActive($scriptName) {
        $this->socket->puts('SETACTIVE "'.$scriptName.'"'."\r\n");
        $this->getTillOk();
    }

    /**
     * GetScript
     * Get the script provided by the scriptName
     *
     * @param string $scriptName
     * @return string
     */
    public function GetScript($scriptName) {
        $this->socket->puts('GETSCRIPT "'.$scriptName.'"'."\r\n");
        $return = $this->getTillOk();
        return $return;
    }

    /**
     * PutScript
     * Writes a script to the server
     * @param string $scriptName the name of the script
     * @param string $script the actual script
     * @return bool
     */
    public function PutScript($scriptName,$script) {
        $this->socket->puts('PUTSCRIPT "'.$scriptName.'" {'.strlen($script).'}'."\r\n");
        $this->socket->puts($script."\r\n");
        $return = $this->getTillOk();
        if (preg_match('/^ERR /', $return)) {
            return false;
        } elseif (preg_match('/^OK /', $return)) {
            return true;
        } elseif (preg_match('/^NO {(\d*)}/', $return,$result)) {
            $err = '';
            do {
                $line = $this->socket->gets();
                $err .= $line;
            } while (strlen($err) < $result[1]);
            return false;
        }
    }

    /**
     * logout
     * Send a Logout command.
     */
    public function logout() {
        $this->socket->puts('LOGOUT' . "\r\n");
        $this->getTillOk();
    }

    /**
     * getTillOk
     * get From the socket until we reach an OK|ERR|NO response
     * 
     * @return string
     */
    protected function getTillOk() {
        $return = '';
        do {
            $line = $this->socket->gets();
            $return .= $line;
        } while (!preg_match('/^(OK|NO|ERR) /i',$line, $matches));

        switch(strtoupper($matches[1])) {
            case 'OK':
                $this->lastReturn = self::OK;
                break;
            case 'NO':
                $this->lastReturn = self::NO;
                break;
            case 'ERR':
                $this->lastReturn = self::ERR;
                break;
            default:
                throw new phpmanagesieve_Mail_ManageSieve_Exception("UNKNOWN Exception");
        }

        return $return;
    }
}
