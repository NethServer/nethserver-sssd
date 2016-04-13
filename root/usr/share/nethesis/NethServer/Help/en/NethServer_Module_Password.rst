=================
Password policies
=================

Modify the password policies (the default policy is strong).

none
----
    The none policy requires that the password must comply with the following rule:

    * There is no specific control over the password entered, but minimum length is 7 characters

strong
------
    The strong policy requires that the password must comply with the following rules:

    * Minimum length of 7 characters
    * Contain at least 1 number
    * Contain at least 1 uppercase character
    * Contain at least 1 lowercase character
    * Contain at least 1 special character
    * At least 5 different characters
    * Must be not present in the dictionaries of common words
    * Must be different from the username
    * Can not have repetitions of patterns formed by 3 or more characters (for example the password As1.$ AS1. $ is invalid)

**WARNING**: Changing the default policies is highly discouraged. The use of weak passwords often lead to compromised servers by external attackers.

Strong password policy for Users
    Set a strong policy for Users Password (unchecked is 'none')

Strong password policy for Admin
    Set a strong policy for the Admin Password (unchecked is 'none')

The Maximum Password Age
    Maximum number of days for which you can keep the same password (default: 180)

The Minimum Password Age
    Minimum number of days for which you are forced to keep the same password (default: 0)

The number of days before sending a reminder
    Number of days on which the warning is sent by email (default: 7)
