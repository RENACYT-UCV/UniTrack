import { Component, OnInit } from '@angular/core';
import { FlaskService } from '../services/flask.service';
import { UserService } from '../services/user.service';
import { Reporte } from '../services/reporte';
import { AlertController } from '@ionic/angular';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
})
export class HomePage implements OnInit {
  
  reportes: Reporte[] = []; // Utiliza la interfaz Reporte para definir el tipo de reportes
  verificationResult: string = '';
  nombrecompleto: string= ''; 
  nombre: string = ''; 
  currentUser: any;
  codigo: string= ''; 
  correo: string = ''; 
  edad: string = ''; 
  sexo: string = ''; 
  

  constructor(private userService: UserService, private flaskservice: FlaskService,  private alertController: AlertController) {
  
  }

 
  ngOnInit() {
   
    this.currentUser = this.userService.getCurrentUser();
    console.log(this.currentUser)
    if (this.currentUser) {
      this.nombrecompleto = `${this.currentUser.nombres} ${this.currentUser.apellidos}`;
      this.codigo= this.currentUser.codigo_admin; 
      this.correo = this.currentUser.correo; 
      this.edad = this.currentUser.edad; 
      this.sexo = this.currentUser.sexo; 
    }

    console.log('Nombre completo:', this.nombrecompleto);
    console.log('Código:', this.codigo);
    console.log('Correo:', this.correo);
    console.log('Edad:', this.edad);
    console.log('Sexo:', this.sexo);
  }

  async verifyQR() {
    try {
        const result = await this.flaskservice.verifyQR();
        this.verificationResult = result.verified ? '¡Verificación correcta, puede ingresar!' : '¡ERROR EN LA VERIFICACIÓN!';
        this.showAlert();
    } catch (error) {
      console.error('Error verificando QR:', error);
      this.verificationResult = 'Error verificando QR';
      this.showAlert();
    }
    
    
  }


  

  async showAlert() {
    if (this.verificationResult) {
      const alert = await this.alertController.create({
        header: 'Verificación',
        message: this.verificationResult,
        buttons: ['Aceptar']
      });

      await alert.present();
    }
  }

}
