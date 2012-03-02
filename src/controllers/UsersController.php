<?php
namespace Blocks;

/**
 * Handles user management tasks including registering, etc., etc.
 */
class UsersController extends BaseController
{
	/**
	 * All user actions require the user to be logged in
	 */
	public function init()
	{
		$this->requireLogin();
	}

	/**
	 * @param $user
	 * @param $site
	 * @return bool
	 */
	public function actionSendRegistrationEmail($user, $site)
	{
		$this->requireAjaxRequest();
		$this->requirePostRequest();

		if (($emailStatus = Blocks::app()->email->sendRegistrationEmail($user, $site)) == true)
		{
			return true;
		}

		return false;
	}

	public function actionSave()
	{
		$this->requirePostRequest();

		$existingUser = false;

		// Are we editing an existing user?
		$postUserId = Blocks::app()->request->getPost('user_id');
		if ($postUserId)
		{
			$existingUser = true;
			$user = Blocks::app()->users->getById($postUserId);
		}

		if (empty($user))
			$user = new User();

		if (Blocks::app()->request->getPost('suspend', null) !== null)
		{
			$user->status = UserAccountStatus::Suspended;
		}
		else if (Blocks::app()->request->getPost('validationEmail', null) !== null)
		{
			//Blocks::app()->users->sendRegistrationEmail(user, site);
		}
		else if (Blocks::app()->request->getPost('unsuspend', null) !== null)
		{
			$user->status = UserAccountStatus::Active;
		}
		else if (Blocks::app()->request->getPost('unlock', null) !== null)
		{
			$user->status = UserAccountStatus::Active;
		}
		else if (Blocks::app()->request->getPost('delete', null) !== null)
		{
			// delete logic.
		}
		else if (Blocks::app()->request->getPost('save', null) !== null)
		{
			$user->username = Blocks::app()->request->getPost('username');
			$user->first_name = Blocks::app()->request->getPost('first_name');
			$user->last_name = Blocks::app()->request->getPost('last_name');
			$user->email = Blocks::app()->request->getPost('email');
			$user->admin = (Blocks::app()->request->getPost('admin') === 'y');
			$user->html_email = (Blocks::app()->request->getPost('html_email') === 'y');
			$user->status = Blocks::app()->request->getPost('status');
			$user->password_reset_required = (Blocks::app()->request->getPost('password_reset') === 'y');

			$sendValidationEmail = (Blocks::app()->request->getPost('send_validation_email') === 'y');

			if ($user->validate())
			{
				if (!$existingUser)
				{
					$user = Blocks::app()->users->registerUser($user, null, true);

					if ($user !== null)
					{
						if ($sendValidationEmail)
						{
							$site = Blocks::app()->sites->currentSite;
							if (($emailStatus = Blocks::app()->email->sendRegistrationEmail($user, $site)) == true)
							{
								// registered and sent email
								Blocks::app()->user->setMessage(MessageStatus::Success, 'Successfully registered user and sent registration email.');
							}
							else
							{
								// registered but there was a problem sending the email.
								Blocks::app()->user->setMessage(MessageStatus::Notice, 'Successfully registered user, but there was a problem sending the email: '.$emailStatus);
							}
						}
						else
						{
							// registered user with no email validation
							Blocks::app()->user->setMessage(MessageStatus::Success, 'Successfully registered user.');
						}
					}
					else
					{
						// there was a problem registering the user.
						Blocks::app()->user->setMessage(MessageStatus::Error, 'There was a problem registering the user.  Check your log files.');
					}
				}
				else
					$user->save(false);

				if ($existingUser)
					Blocks::app()->user->setMessage(MessageStatus::Success, 'User saved successfully.');
			}
		}

			$url = Blocks::app()->request->getPost('redirect');
			if ($url !== null)
				$this->redirect($url);


		$this->loadRequestedTemplate(array('theUser' => $user));
	}
}

