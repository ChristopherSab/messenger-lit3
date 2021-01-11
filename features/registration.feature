Feature: Registration
  In order to gain access to chat application for the first time
  As a user
  I need to be able to register

  Background:
    Given I am on "/register"

    Scenario: Registering A New User
      And I fill in "Username" with "Donkey"
      And I fill in "Password" with "Password"
      And I fill in "Email" with "donkey@wren.com"
      And I press "Register"
      Then I should be on "/query"

    Scenario: Registering With An Existing Username And Email
      Given there is a user "TestUser" with email "TestUser@wren.com" and Password "Password"
      And I fill in "Username" with "TestUser"
      And I fill in "Password" with "Password"
      And I fill in "Email" with "TestUser@wren.com"
      And I press "Register"
      Then I should see "There is already an account with this username"
      Then I should see "There is already an account with this email address"





