<?php
/**
 * Login
 *
 * Copyright 2009 by Jason Coward <jason@collabpad.com> and Shaun McCormick
 * <shaun@collabpad. com>
 *
 * Login is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * Login is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Login; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package login
 */
/**
 * MODx Login Class
 *
 * @author Jason Coward <jason@collabpad.com>
 * @author Shaun McCormick <shaun@collabpad.com>
 * @copyright Copyright &copy; 2009
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License
 * version 2 or (at your option) any later version.
 * @package login
 */
class login {

    /**#@+
     * Creates an instance of the Login class.
     *
     * @param modX &$modx A reference to the modX instance.
     * @param array $config An array of configuration parameters.
     * @return Login
     */
    function Login(&$modx,$config = array()) {
        $this->__construct($modx,$config);
    }
    /** @ignore */
    function __construct(&$modx,$config = array()) {
        $this->modx =& $modx;
        $this->config = array_merge(array(
        ),$config);
    }
    /**#@-*/

    /**
     * Gets the current Request URI.
     *
     * @access public
     * @return string The request URI, with Login-specific code stripped.
     */
    function getRequestURI() {
        return str_replace(array('?service=logout','&service=logout'),'',$_SERVER['REQUEST_URI']);
    }

    /**
     * Gets a user by a specific field in the table.
     *
     * @access public
     * @param string $field The column to grab by.
     * @param string $value The value to search by.
     * @param string $alias Optional; allows searching by a separate class
     * alias. Defaults to modUser.
     * @return modUser/null Returns a modUser object if successfull; null if
     * not.
     */
    function getUserByField($field,$value,$alias = 'modUser') {
        $c = $this->modx->newQuery('modUser');
        $c->select('modUser.*, modUserProfile.email AS email');
        $c->innerJoin('modUserProfile','modUserProfile');
        $c->where(array(
            $alias.'.'.$field => $value,
        ));
        return $this->modx->getObject('modUser',$c);
    }

    /**
     * Sends an email based on the specified information and templates.
     *
     * @access public
     * @param string $email The email to send to.
     * @param string $name The name of the user to send to.
     * @param string $subject The subject of the email.
     * @param array $properties A collection of properties.
     * @return array
     */
    function sendEmail($email,$name,$subject,$properties = array()) {
        if (empty($properties['tpl'])) $properties['tpl'] = 'lgnForgotPassEmail';
        if (empty($properties['tplType'])) $properties['tplType'] = 'modChunk';

        $msg = $this->getChunk($properties['tpl'],$properties,$properties['tplType']);

        $this->modx->getService('mail', 'mail.modPHPMailer');
        $this->modx->mail->set(MODX_MAIL_BODY, $msg);
        $this->modx->mail->set(MODX_MAIL_FROM, $this->modx->getOption('emailsender'));
        $this->modx->mail->set(MODX_MAIL_FROM_NAME, $this->modx->getOption('site_name'));
        $this->modx->mail->set(MODX_MAIL_SENDER, $this->modx->getOption('emailsender'));
        $this->modx->mail->set(MODX_MAIL_SUBJECT, $subject);
        $this->modx->mail->address('to', $email, $name);
        $this->modx->mail->address('reply-to', $this->modx->getOption('emailsender'));
        $sent = $this->modx->mail->send();
        $this->modx->mail->reset();

        return $sent;
    }

    /**
     * Generates a random password.
     *
     * @access public
     * @param integer $length The length of the generated password.
     * @return string The newly-generated password.
     */
    function generatePassword($length=8) {
        $pword = '';
        $charmap = '0123456789bcdfghjkmnpqrstvwxyz';
        $i = 0;
        while ($i < $length) {
            $char = substr($charmap, rand(0, strlen($charmap)-1), 1);
            if (!strstr($pword, $char)) {
                $pword .= $char;
                $i++;
            }
        }
        return $pword;
    }

    /**
     * Helper function to get a chunk or tpl by different methods.
     *
     * @access public
     * @param string $name The name of the tpl/chunk.
     * @param array $properties The properties to use for the tpl/chunk.
     * @param string $type The type of tpl/chunk. Can be embedded,
     * modChunk, file, or inline. Defaults to modChunk.
     * @return string The processed tpl/chunk.
     */
    function getChunk($name,$properties,$type = 'modChunk') {
        $output = '';
        switch ($type) {
            case 'embedded':
                if (!$this->modx->user->isAuthenticated($this->modx->context->get('key'))) {
                    $modx->setPlaceholders($properties);
                }
                break;
            case 'modChunk':
                $output .= $this->modx->getChunk($name, $properties);
                break;
            case 'file':
                $output .= file_get_contents($name);
                $this->modx->setPlaceholders($properties);
                break;
            case 'inline':
            default:
                /* default is inline, meaning the tpl content was provided directly in the property */
                $output .= $name;
                $this->modx->setPlaceholders($properties);
                break;
        }
        return $output;
    }
}