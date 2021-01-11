Feature: Authentication
  In order to gain access to chat application
  As a user
  I need to be able to login

  Background:
    Given I am on "/login"

  Scenario: Logging in
    Given there is a user "TestUser" with password "Password"
    And I fill in "Username" with "TestUser"
    And I fill in "Password" with "Password"
    And I press "Sign in"
    And print last response
    Then I should be on "/query"
