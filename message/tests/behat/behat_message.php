<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat message-related steps definitions.
 *
 * @package    core_message
 * @category   test
 * @copyright  2013 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;
use Behat\Gherkin\Node\PyStringNode as PyStringNode;
use \Behat\Mink\Exception\ExpectationException;

/**
 * Messaging system steps definitions.
 *
 * @package    core_message
 * @category   test
 * @copyright  2013 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_message extends behat_base {

    /**
     * View the contact information of a user in the messages ui.
     *
     * @Given /^I view the "(?P<user_full_name_string>(?:[^"]|\\")*)" contact in the message area$/
     * @param string $userfullname
     */
    public function i_view_contact_in_messages($userfullname) {
        \behat_hooks::set_step_readonly(false);

        // Visit home page and follow messages.
        $this->i_select_user_in_messaging($userfullname);

        $this->execute('behat_general::i_click_on_in_the',
            array(
                "//button[@data-action='view-contact-profile']
                [contains(normalize-space(.), '" . $this->escape($userfullname) . "')]",
                'xpath_element',
                ".messages-header",
                "css_element",
            )
        );

        $this->execute('behat_general::wait_until_the_page_is_ready');
    }

    /**
     * Select a user in the messaging UI.
     *
     * @Given /^I select "(?P<user_full_name_string>(?:[^"]|\\")*)" user in messaging$/
     * @param string $userfullname
     */
    public function i_select_user_in_messaging($userfullname) {
        \behat_hooks::set_step_readonly(false);

        // Visit home page and follow messages.
        $this->execute("behat_general::i_am_on_homepage");

        $this->execute("behat_navigation::i_follow_in_the_user_menu", get_string('messages', 'message'));

        $this->execute('behat_general::i_click_on', array("contacts-view", 'message_area_action'));

        $this->execute('behat_general::wait_until_the_page_is_ready');

        $this->execute('behat_forms::i_set_the_field_to',
            array(get_string('searchforuserorcourse', 'message'), $this->escape($userfullname))
        );

        $this->execute('behat_general::wait_until_the_page_is_ready');

        // Need to limit the click to the search results because the 'view-contact-profile' elements
        // can occur in two separate divs on the page.
        $this->execute('behat_general::i_click_on_in_the',
            array(
                "//div[@data-action='view-contact-msg']
                [./div[contains(normalize-space(.), '" . $this->escape($userfullname) . "')]]",
                'xpath_element',
                "[data-region='messaging-area'] [data-region='search-results-area']",
                "css_element",
            )
        );

        $this->execute('behat_general::wait_until_the_page_is_ready');
    }


    /**
     * Sends a message to the specified user from the logged user. The user full name should contain the first and last names.
     *
     * @Given /^I send "(?P<message_contents_string>(?:[^"]|\\")*)" message to "(?P<user_full_name_string>(?:[^"]|\\")*)" user$/
     * @param string $messagecontent
     * @param string $userfullname
     */
    public function i_send_message_to_user($messagecontent, $userfullname) {
        \behat_hooks::set_step_readonly(false);

        $this->i_select_user_in_messaging($userfullname);

        $this->execute('behat_forms::i_set_the_field_with_xpath_to',
            array("//textarea[@data-region='send-message-txt']", $this->escape($messagecontent))
        );

        $this->execute("behat_forms::press_button", get_string('send', 'message'));
    }

    /**
     * Select messages from a user in the messaging ui.
     *
     * @Given /^I send "(?P<message_contents_string>(?:[^"]|\\")*)" message in the message area$/
     * @param string $messagecontent
     */
    public function i_send_message_in_the_message_area($messagecontent) {
        \behat_hooks::set_step_readonly(false);

        $this->execute('behat_forms::i_set_the_field_with_xpath_to',
            array("//textarea[@data-region='send-message-txt']", $this->escape($messagecontent))
        );

        $this->execute("behat_forms::press_button", get_string('send', 'message'));
    }

    /**
     * Checks to see if a message exists by message subject for a given user
     *
     * @Given /^the message "([^"]*)" exists for "([^"]*)" user$/
     * @param string $messagesubject
     * @param string $username
     */
    public function the_message_exists_for_user($messagesubject, $username) {
        \behat_hooks::set_step_readonly(true);
        global $DB;

        $sql = "SELECT m.id
                  FROM {message} m
                  JOIN {user} u ON u.id = m.useridto
                 WHERE m.subject = :subject
                   AND u.username = :username";

        $params = array(
            'subject' => $messagesubject,
            'username' => $username
        );

        if (!$DB->record_exists_sql($sql, $params)) {
            throw new ExpectationException('The message with subject ' . $messagesubject . ' was not found for user ' . $username, $this->getSession());
        }
    }

    /**
     * Checks to see if a message does not contain text for a given user
     *
     * @Given /^the message "([^"]*)" does not contain "([^"]*)" for "([^"]*)" user$/
     * @param string $messagesubject
     * @param string $messagecontains
     * @param string $username
     */
    public function the_message_does_not_contain_for_user($messagesubject, $messagecontains, $username) {
        \behat_hooks::set_step_readonly(true);
        global $DB;

        $sql = "SELECT m.id
                  FROM {message} m
                  JOIN {user} u ON u.id = m.useridto
                 WHERE m.subject = :subject
                   AND " . $DB->sql_like('m.fullmessage', ':contains', false, true, true) . "
                   AND u.username = :username";

        $params = array(
            'subject' => $messagesubject,
            'contains' => '%' . $DB->sql_like_escape($messagecontains) . '%',
            'username' => $username
        );

        if (!$DB->record_exists_sql($sql, $params)) {
            throw new ExpectationException($DB->sql_like('m.fullmessage', ':contains', false) . 'The message with subject ' . $messagesubject . ' should not contain ' . $messagecontains . ' for user ' . $username, $this->getSession());
        }
    }

    /**
     * Checks to see if a message contains text for a given user
     *
     * @Given /^the message "([^"]*)" contains "([^"]*)" for "([^"]*)" user$/
     * @param string $messagesubject
     * @param string $messagecontains
     * @param string $username
     */
    public function the_message_contains_for_user($messagesubject, $messagecontains, $username) {
        \behat_hooks::set_step_readonly(true);
        global $DB;

        $sql = "SELECT m.id
                  FROM {message} m
                  JOIN {user} u ON u.id = m.useridto
                 WHERE m.subject = :subject
                   AND " . $DB->sql_like('m.fullmessage', ':contains', false) . "
                   AND u.username = :username";

        $params = array(
            'subject' => $messagesubject,
            'contains' => '%' . $DB->sql_like_escape($messagecontains) . '%',
            'username' => $username
        );

        if (!$DB->record_exists_sql($sql, $params)) {
            throw new ExpectationException($DB->sql_like('m.fullmessage', ':contains') . 'The message with subject ' . $messagesubject . ' does not contain ' . $messagecontains . ' for user ' . $username, $this->getSession());
        }
    }

    /**
     * Checks to see if a message contains multiline text for a given user
     *
     * @Given /^the message "([^"]*)" for "([^"]*)" user contains multiline:?$/
     * @param string $messagesubject
     * @param string $messagecontains
     * @param string $username
     */
    public function the_message_contains_multiline_for_user($messagesubject, $username, PyStringNode $messagecontains) {
        \behat_hooks::set_step_readonly(true);
        global $DB;

        $sql = "SELECT m.id
                  FROM {message} m
                  JOIN {user} u ON u.id = m.useridto
                 WHERE m.subject = :subject
                   AND " . $DB->sql_like('m.fullmessage', ':contains') . "
                   AND u.username = :username";

        $params = array(
            'subject' => $messagesubject,
            'contains' => '%' . $DB->sql_like_escape($messagecontains) . '%',
            'username' => $username
        );

        if (!$DB->record_exists_sql($sql, $params)) {
            throw new ExpectationException($DB->sql_like('m.fullmessage', ':contains') . 'The message with subject ' . $messagesubject . ' does not contain ' . $messagecontains . ' for user ' . $username, $this->getSession());
        }
    }

}
