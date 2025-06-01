import { Component, OnInit } from '@angular/core';
import { EnvioCorreoService } from 'src/app/services/envio-correo.service';  // Asegúrate de que el servicio esté importado correctamente
import { NavController } from '@ionic/angular';  // Importa NavController para redirigir

@Component({
  selector: 'app-contrasena',  // Asegúrate de que el selector coincida con el archivo
  templateUrl: './contrasena.page.html',  // Este archivo debe coincidir con tu HTML
  styleUrls: ['./contrasena.page.scss'],  // Este archivo debe coincidir con tus estilos SCSS
})
export class ContrasenaPage implements OnInit {
  newPassword: string = '';  // Nueva contraseña
  confirmPassword: string = '';  // Confirmación de la nueva contraseña

  constructor(private userService: EnvioCorreoService, private navCtrl: NavController) {}

  // Función para cambiar la contraseña
  resetPassword() {
    if (this.newPassword && this.newPassword === this.confirmPassword) {
      this.userService.resetPassword(this.newPassword).subscribe(
        (response) => {
          // Si la contraseña se cambia correctamente, redirigimos al login
          this.navCtrl.navigateRoot('/login');
        },
        (error) => {
          console.error(error);
          alert('Hubo un error al intentar actualizar tu contraseña. Por favor, inténtalo de nuevo.');
        }
      );
    } else {
      alert('Las contraseñas no coinciden o están vacías.');
    }
  }

  // Función para redirigir al login
  irAlLogin() {
    this.navCtrl.navigateRoot('/login');  // Redirige a la página de login
  }

  ngOnInit() {}
}
