<?php
    namespace App\Traits;

    use App\Models\User;

    trait Users {

        /*
        * Check whether email address exists
        */
        public function emailAddressExists($email)
        {
            $flag=false;

            $email=User::where('email',$email)->count();

            if($email>0){
                $flag=true;
            }

            return $flag;
        }

        /*
        * Check whether username exists
        */
        public function usernameExists($username)
        {
            $flag=false;

            $username=User::where('username',$username)->count();

            if($username>0){
                $flag=true;
            }

            return $flag;
        }

        /*
        * Check if email belongs to the user
        */
        public function emailBelongsToUser($user_id,$email)
        {
            $email_belongs_to_user=false;

            $user=User::where('id',$user_id)->where('email',$email)->count();

            if($user>0){
                $email_belongs_to_user=true;
            }

            return $email_belongs_to_user;
        }

        /*
        * Check if username belongs to the user
        */
        public function usernameBelongsToUser($user_id,$username)
        {
            $username_belongs_to_user=false;

            $user=User::where('id',$user_id)->where('username',$username)->count();

            if($user>0){
                $username_belongs_to_user=true;
            }

            return $username_belongs_to_user;
        }
    }