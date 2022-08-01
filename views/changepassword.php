<?php
    require_once 'core/init.php';

    $user = new User();

    if(!$user->isLoggedIn()) {
        Redirect::to('index.php');
    }

    if(Input::exists()) {
        if(Token::check(Input::get('token'))) {
            $validate = new Validate();
            $validation = $validate->check($_POST, array(
                'cur_password' => array(
                    'required' => true,
                    'min' => 6),
                'new_password' => array(
                    'required' => true,
                    'min' => 6
                ),
                'renew_password' => array(
                    'required' => true,
                    'min' => 6,
                    'matches' => 'new_password'
                )
            ));

            if($validation->passed()) {
                // change of password
                if(Hash::make(Input::get('cur_password'), $user->data()->salt !== $user->data()->password)) {
                    echo 'Your current password is wrong.';
                }else {
                    $salt = Hash::salt(32);
                    $user->update(array(
                        'password' => Hash::make(Input::get('new_password'), $salt),
                        'salt' => $salt
                    ));

                    Session::flash('home', 'Your password has been changed!');
                    Redirect::to('index.php');
                }
            }else {
                foreach($validation->errors() as $error) {
                    echo $error, '<br>';
                }
            }
        }
    }
?>

<form action="" method="post">
    <div class="field">
        <label for="cur_password">Current Password:</label>
        <input type="password" name="cur_password" id="cur_password">
    </div>

    <div class="field">
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password">
    </div>

    <div class="field">
        <label for="renew_password">Retype New password:</label>
        <input type="password" name="renew_password" id="renew_password">
    </div>

    <input type="submit" value="Change">
    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
</form>