import { Component, OnInit } from '@angular/core';
import { UserService } from '../services/user.service';
import { Router } from '@angular/router';
@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit {

  correo: string=''; 
 contrasena: string =''; 
 showPassword: boolean = false;
  constructor(private userService: UserService, private router: Router) { }
  login(){
    this.userService.loginUser(this.correo, this.contrasena).subscribe(
      response => {
        if (response.error){
          //Manejar error de autentificacion
          console.error(response.error);
        } else{
          //Manejar exito de autentificacion
          console.log('Login exitoso', response); 
          // Almacenar el usuario autenticado
          this.userService.setCurrentUser(response);
          
          //Redirigir a otra interfaz
          this.router.navigate(['/home'])
          return response;
        }
      },
      error => {
        console.error('Error de conexion', error); 
      }
    );
  }
  ngOnInit() {
  }

  togglePassword() {
    this.showPassword = !this.showPassword;
  }

}
