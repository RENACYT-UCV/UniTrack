import { Component } from '@angular/core';
import { UserService } from '../services/user.service';
import { Router } from '@angular/router';  // Importamos Router para la navegación

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage {
  correo: string = '';
  contrasena: string = '';
  vista: string = 'login';

  constructor(private userService: UserService, private router: Router) {}

  // Método para iniciar sesión
  login() {
    if (this.correo && this.contrasena) {
      this.userService.loginUser(this.correo, this.contrasena).subscribe(
        (response) => {
          if (response.error) {
            alert('Error: ' + response.error);
          } else {
            this.userService.setCurrentUser(response.user);
            // Aquí puedes redirigir al usuario a otra página
            // Por ejemplo: this.router.navigate(['/dashboard']);
          }
        },
        (error) => {
          alert('Hubo un error al intentar iniciar sesión. Por favor, intente de nuevo.');
        }
      );
    } else {
      alert('Por favor ingrese su correo y contraseña.');
    }
  }

  // Método para cambiar de vista (registro o olvido de contraseña)
  cambiarVista(vista: string) {
    if (vista === 'register') {
      this.router.navigate(['/register']);  // Redirige a la página de registro
    } else if (vista === 'forgot') {
      this.router.navigate(['/contrasena']);  // Redirige a la página de cambio de contraseña
    }
  }

  // Método para redirigir a la página de cambio de contraseña
  irACambioContrasena() {
    this.router.navigate(['/contrasena']); // Redirige a la página de cambio de contraseña
  }
}
