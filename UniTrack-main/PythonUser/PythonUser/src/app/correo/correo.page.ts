import { Component, OnInit } from '@angular/core';
import { EnvioCorreoService } from '../services/envio-correo.service';  // Asegúrate de que el servicio esté importado correctamente
import { NavController } from '@ionic/angular';  // Necesitamos NavController para la navegación

@Component({
  selector: 'app-correo',  // Asegúrate de que el selector coincida con el archivo
  templateUrl: './correo.page.html',  // Este archivo debe coincidir con tu HTML
  styleUrls: ['./correo.page.scss'],  // Este archivo debe coincidir con tus estilos SCSS
})
export class CorreoPage implements OnInit {
  email: string = '';  // Definimos la variable para almacenar el correo del usuario

  constructor(
    private userService: EnvioCorreoService,  // El servicio para manejar la verificación del correo
    private navCtrl: NavController  // Necesitamos NavController para redirigir
  ) {}

  ngOnInit() {}

  // Función para enviar el código de verificación
  sendVerificationCode() {
    if (this.email) {
      this.userService.sendVerificationCodee(this.email).subscribe(
        (response) => {
          // Redirige al usuario a la página de verificación
          this.navCtrl.navigateForward('/verificar');
        },
        (error) => {
          console.error(error);
          alert('Hubo un error al enviar el código. Por favor, intente de nuevo.');
        }
      );
    } else {
      alert('Por favor, ingresa un correo electrónico válido.');
    }
  }

  // Función para redirigir al login
  irAlLogin() {
    this.navCtrl.navigateRoot('/login');  // Redirige a la página de login
  }
}
