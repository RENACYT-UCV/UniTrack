import { Component } from '@angular/core';
import { UserService } from '../services/user.service';
import { Router } from '@angular/router';  

@Component({
  selector: 'app-register',  
  templateUrl: './register.page.html', 
  styleUrls: ['./register.page.scss'],
})
export class RegisterPage {  
  nombres: string = '';
  apellidos: string = '';
  correo: string = '';
  codigo_estudiante: string = '';
  correoA: string = '';
  carrera: string = '';
  ciclo: string = '';
  edad: string = '';
  sexo: string = '';
  contrasena: string = '';
  confirmarContrasena: string = '';
  vista: string = 'register';

  constructor(private userService: UserService, private router: Router) {}

  registrarse() {
    if (
      this.nombres &&
      this.apellidos &&
      this.correo &&
      this.codigo_estudiante &&
      this.correoA &&
      this.carrera &&
      this.ciclo &&
      this.edad &&
      this.sexo &&
      this.contrasena &&
      this.contrasena === this.confirmarContrasena
    ) {
      this.userService.registerUser(
        this.nombres,
        this.apellidos,
        this.correo,
        this.codigo_estudiante,
        this.correoA,
        this.carrera,
        this.ciclo,
        this.edad,
        this.sexo,
        this.contrasena
      ).subscribe(
        (response: any) => {
          if (response.error) {
            alert('Error: ' + response.error);
          } else {
            this.userService.setCurrentUser(response.user);
          }
        },
        (error: any) => {
          alert('Hubo un error al intentar registrarte. Por favor, intente de nuevo.');
        }
      );
    } else {
      alert('Por favor ingresa todos los campos correctamente.');
    }
  }

  irAlLogin() {
    this.router.navigate(['/login']);  
  }
}
