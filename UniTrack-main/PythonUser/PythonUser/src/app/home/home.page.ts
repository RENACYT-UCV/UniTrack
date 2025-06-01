import { Component, OnInit } from '@angular/core';
import { UserService } from '../services/user.service';
import { NavController } from '@ionic/angular';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { FlaskApiService } from '../services/flask-api.services.service';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-home',
  templateUrl: './home.page.html',
  styleUrls: ['./home.page.scss'],
})
export class HomePage implements OnInit {
  nombrecompleto: string = ''; 
  qrImageUrl: string = ''; // URL de la imagen del QR
  isEntrada: boolean = true;
  toastMessage: string | null = null;
  currentUser: any; 

  constructor(
    private userService: UserService,
    private navCtrl: NavController,
    private flask: FlaskApiService,
    private toastController: ToastController
  ) {}

  ngOnInit() {
    const user = localStorage.getItem('currentUser');
    this.currentUser = user ? JSON.parse(user) : null;
    if (this.currentUser && this.currentUser.user) {
      this.nombrecompleto = `${this.currentUser.user.nombres} ${this.currentUser.user.apellidos}`;
    } else {
      console.error('Propiedades de usuario no están definidas.');
    }
  }

  async generarQR() {
    if (!this.currentUser || !this.currentUser.user || !this.currentUser.user.idUsuario) {
      console.error('Datos de usuario incompletos.');
      return;
    }

    const userId = this.currentUser.user.idUsuario;
    const nombre = this.currentUser.user.nombres;
    const correo = this.currentUser.user.correo;
    const correoA = this.currentUser.user.correoA;
    const modo = this.isEntrada ? 'Entrada' : 'Salida';

    try {
      // Ahora pasamos todos los parámetros requeridos
      const reportResult = await this.flask.storeTempUser(userId, nombre, correo, modo, correoA).toPromise();
      this.qrImageUrl = await this.flask.generarQR();
      this.navCtrl.navigateForward('/qr', {
        queryParams: { qrImageUrl: this.qrImageUrl }
      });
    } catch (error) {
      const toast = await this.toastController.create({
        message: 'No puede generar el mismo modo de QR en un corto tiempo.',
        duration: 3000,
        position: 'bottom'
      });
      await toast.present();
    }
  }

  async onToggleChange() {
    this.toastMessage = this.isEntrada ? 'Modo Entrada seleccionado' : 'Modo Salida seleccionado';
    const toast = await this.toastController.create({
      message: this.toastMessage,
      duration: 2000,
      position: 'bottom'
    });
    await toast.present();
  }
}
