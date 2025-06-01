import { Component } from '@angular/core';
import { UserService } from '../services/user.service';
import { Router } from '@angular/router';  // Importamos Router para la navegación

@Component({
  selector: 'app-register',  // Deja 'app-register' porque se debe mantener el nombre según tu estructura.
  templateUrl: './register.page.html',  // Mantén la referencia a 'register.page.html'
  styleUrls: ['./register.page.scss'],
})
export class RegisterPage {  // Aquí no cambiamos el nombre porque debe coincidir con el nombre del archivo.
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
            // Aquí puedes redirigir al usuario a otra página después del registro
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

  // Función para redirigir al login
  irAlLogin() {
    this.router.navigate(['/login']);  // Redirige a la página de login
  }
}
