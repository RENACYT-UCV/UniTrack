import { Component, OnInit } from '@angular/core';
import { EnvioCorreoService } from '../services/envio-correo.service';  // Asegúrate de importar correctamente el servicio
import { NavController } from '@ionic/angular';  // Necesitamos NavController para la navegación
import { Router } from '@angular/router';

@Component({
  selector: 'app-correo',
  templateUrl: './correo.page.html',
  styleUrls: ['./correo.page.scss'],
})
export class CorreoPage implements OnInit {
  email: string = '';  // Definimos la variable para almacenar el correo del usuario

  constructor(
    private userService: EnvioCorreoService,  // El servicio para manejar la verificación del correo
    private navCtrl: NavController,  // Para la navegación
    private router: Router  // Asegúrate de que el router esté importado
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
