<?php
class PerfilController extends BaseController {
	public function actualizar_password()
    {
        $registerData = array(
            'actual'                =>  Input::get('actual'),
            'password'              =>  Input::get('nueva'),
            'password_confirmation' =>  Input::get('repNueva')
        );

        $rules = array(
            'actual'                  => 'required',
            'password'              => 'required|confirmed|min:4'
        );
        $password_act = Input::get('actual');


        $messages = array(
            'actual.required'         => "El campo <b>Contraseña Actual</b> es Obligatorio.",
            'min'                   => 'El campo <b>Nueva Contraseña</b> no puede tener menos de :min carácteres.',
            'confirmed'             => 'La <b>Nueva Contraseña</b> y la <b>Repetición de la Nueva Contraseña</b>  no coinciden.'
        );

        $validation = Validator::make($registerData, $rules, $messages);
        if ($validation->fails())
        {
            return Response::json(array(
                'success' => false,
                'mensaje' => "Error en la información ingresada!"
            ));
        }
        else
        {
            if (Hash::check($password_act, Auth::usuario()->user()->password))
            {

                $user = User::find(Auth::usuario()->user()->id);
                $user->password = Hash::make($registerData['password']);
                $user->save();

                return Response::json(array(
                    'success' => true,
                    'mensaje' => "La Contraseña fue actualizada correctamente"
                ));
            }
            else
            {

                return Response::json(array(
                    'success' => false,
                    'mensaje' =>"La Contraseña Actual ingresada es incorrecta!"
                ));
            }
        }
    }

}
