import { Component, OnInit } from '@angular/core';
import { UserService } from '../services/user.service';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-register',
  templateUrl: './register.page.html',
  styleUrls: ['./register.page.scss'],
})
export class RegisterPage implements OnInit {

  nombres: string = '';
  apellidos: string = '';
  correo: string = '';
  codigo_admin: string = '';
  edad: string= ''; 
  sexo: string= ''; 
  contrasena: string = '';
  
  constructor(private userService: UserService, private router: Router, private toastController: ToastController) { }

  onSubmit() {
  // Validaciones básicas
  if (
    !this.nombres ||
    !this.apellidos ||
    !this.correo ||
    !this.codigo_admin ||
    !this.contrasena ||
    !this.edad ||
    !this.sexo
  ) {
    this.presentToast('Todos los campos son obligatorios');
    return;
  }
  if (!this.correo.endsWith('@ucvvirtual.edu.pe')) {
    this.presentToast('El correo debe ser de la universidad');
    return;
  }
  if (this.contrasena.length < 6) {
    this.presentToast('La contraseña debe tener al menos 6 caracteres');
    return;
  }

  // Si todo está bien, llama al servicio
  this.userService.createAdmin(
    this.nombres,
    this.apellidos,
    this.correo,
    this.codigo_admin,
    this.contrasena,
    this.edad,
    this.sexo
  ).subscribe(
    (response: any) => {
      console.log('Administrador registrado con éxito', response);
      this.presentToast('Administrador registrado con éxito');
      this.router.navigate(['/login']);
    },
    (error: any) => {
      console.error('Error al registrar el usuario', error);
      this.presentToast('Error al registrar el usuario');
    }
  );
}

  async presentToast(message: string) {
    const toast = await this.toastController.create({
      message: message,
      duration: 2000,
      position: 'bottom'
    });
    await toast.present();
  }
  ngOnInit() {
  }
  

}
