@block @block_totara_report_table @javascript @totara @totara_reportbuilder
Feature: Report builder table block
  In order to test report builder table block
  As an admin
  I can add and view report table block

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username    | firstname | lastname |
      | teacher1    | Teacher   | 1        |
      | teacher2    | Teacher   | 2        |
      | learner1    | Learner   | 1        |
      | learner2    | Learner   | 2        |
      | learner3    | Learner   | 3        |
      | learner4    | Learner   | 4        |
      | learner5    | Learner   | 5        |
      | learner6    | Learner   | 6        |
      | learner7    | Learner   | 7        |
      | learner8    | Learner   | 8        |
      | learner9    | Learner   | 9        |
    And I log in as "admin"
    And I navigate to "Manage reports" node in "Site administration > Reports > Report builder"
    And I set the following fields to these values:
      | Report Name | User report |
      | Source      | User        |
    And I press "Create report"
    And I set the following fields to these values:
      | Number of records per page | 5 |
    And I press "Save changes"

  Scenario: Test report block navigation without sid
    # Add and configure block without sid
    When I click on "My Learning" in the totara menu
    And I press "Customise this page"
    And I add the "Report table" block
    And I configure the "Report table" block
    And I set the following fields to these values:
      | Block title | Report wo sid |
      | Report | User report |
    And I press "Save changes"
    And I press "Stop customising this page"

    # Change page and check (wo sid)
    And I click on "Username" "link" in the "Report wo sid" "block"
    Then I should see "Admin" in the "Report wo sid" "block"
    And I should see "learner3" in the "Report wo sid" "block"
    And I click on "Next" "link" in the "Report wo sid" "block"
    And I should see "learner4" in the "Report wo sid" "block"
    And I should see "learner8" in the "Report wo sid" "block"
    And I click on "Previous" "link" in the "Report wo sid" "block"
    And I should see "Admin" in the "Report wo sid" "block"
    And I should see "learner3" in the "Report wo sid" "block"

    # Change sorting and check (wo sid)
    And I click on "Username" "link" in the "Report wo sid" "block"
    And I should see "teacher1" in the "Report wo sid" "block"
    And I should see "learner7" in the "Report wo sid" "block"
    And I click on "Username" "link" in the "Report wo sid" "block"
    And I should see "learner3" in the "Report wo sid" "block"
    And I should see "Admin" in the "Report wo sid" "block"

  Scenario: Test report block navigation with and with sid
    # Create saved search for report.
    And I click on "View This Report" "link"
    # User filter field.
    And I set the following fields to these values:
      | user-fullname | learner |
    # "Search" button ambigous with "Search by" form section
    And I press "id_submitgroupstandard_addfilter"
    And I press "Save this search"
    And I set the following fields to these values:
      | Search Name          | LearnerSearch |
      | Let other users view | 1             |
    And I press "Save changes"

    # Add and configure block with sid
    When I click on "My Learning" in the totara menu
    And I press "Customise this page"
    And I add the "Report table" block
    And I configure the "Report table" block
    And I set the following fields to these values:
      | Block title | Report sid |
      | Report | User report |
    And I press "Save changes"
    And I configure the "Report sid" block
    And I set the following fields to these values:
      | Saved search | LearnerSearch |
    And I press "Save changes"
    And I press "Stop customising this page"

    # Change page and check (sid)
    And I click on "Username" "link" in the "Report sid" "block"
    Then I should see "learner1" in the "Report sid" "block"
    And I should see "learner5" in the "Report sid" "block"
    And I click on "Next" "link" in the "Report sid" "block"
    And I should see "learner6" in the "Report sid" "block"
    And I should see "learner9" in the "Report sid" "block"
    And I should not see "Next" in the "Report sid" "block"
    And I click on "Previous" "link" in the "Report sid" "block"
    And I should see "learner1" in the "Report sid" "block"
    And I should see "learner5" in the "Report sid" "block"

    # Change sorting and check (sid)
    And I click on "Username" "link" in the "Report sid" "block"
    And I should see "learner9" in the "Report sid" "block"
    And I should see "learner5" in the "Report sid" "block"
    And I click on "Username" "link" in the "Report sid" "block"
    And I should see "learner4" in the "Report sid" "block"
    And I should see "learner1" in the "Report sid" "block"