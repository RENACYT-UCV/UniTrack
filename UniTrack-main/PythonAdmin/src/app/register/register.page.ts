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
  edad: string = ''; 
  sexo: string = ''; 
  contrasena: string = '';
  isSubmitting = false;

  constructor(
    private userService: UserService,
    private router: Router,
    private toastController: ToastController
  ) { }

  onSubmit() {
    if (this.isSubmitting) return;
    this.isSubmitting = true;

    // Validación de campos vacíos
    if (!this.nombres || !this.apellidos || !this.correo || 
        !this.codigo_admin || !this.edad || !this.sexo || !this.contrasena) {
      this.presentToast('Todos los campos son obligatorios');
      this.isSubmitting = false;
      return;
    }

    // Validación de nombres y apellidos (solo letras y espacios)
    const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    if (!nameRegex.test(this.nombres)) {
      this.presentToast('Los nombres solo pueden contener letras y espacios');
      this.isSubmitting = false;
      return;
    }

    if (!nameRegex.test(this.apellidos)) {
      this.presentToast('Los apellidos solo pueden contener letras y espacios');
      this.isSubmitting = false;
      return;
    }

    // Validación de correo institucional
    const correoRegex = /^[a-zA-Z0-9._%+-]+@ucvvirtual\.edu\.pe$/;
    if (!correoRegex.test(this.correo)) {
      this.presentToast('Debe ingresar un correo institucional válido (@ucvvirtual.edu.pe)');
      this.isSubmitting = false;
      return;
    }

    // Validación de código admin (6-20 caracteres alfanuméricos)
    if (this.codigo_admin.length < 6 || this.codigo_admin.length > 20) {
      this.presentToast('El código de administrador debe tener entre 6 y 20 caracteres');
      this.isSubmitting = false;
      return;
    }

    // Validación de edad (18-99)
    const edadNum = parseInt(this.edad);
    if (isNaN(edadNum) || edadNum < 18 || edadNum > 99) {
      this.presentToast('La edad debe ser un número entre 18 y 99');
      this.isSubmitting = false;
      return;
    }

    // Validación de contraseña (6-50 caracteres, al menos 1 letra y 1 número)
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d).{6,50}$/;
    if (!passwordRegex.test(this.contrasena)) {
      this.presentToast('La contraseña debe tener entre 6 y 50 caracteres e incluir al menos una letra y un número');
      this.isSubmitting = false;
      return;
    }

    // Si pasa todas las validaciones, proceder con el registro
    this.userService.createAdmin(
      this.nombres,
      this.apellidos,
      this.correo,
      this.codigo_admin,
      this.contrasena,
      this.edad,
      this.sexo
    ).subscribe(
      () => {
        this.presentToast('Administrador registrado con éxito', 'success');
        this.router.navigate(['/login']);
        this.isSubmitting = false;
      },
      (error: any) => {
        this.presentToast(error.message || 'Error al registrar el usuario');
        this.isSubmitting = false;
      }
    );
  }

  async presentToast(message: string, color: string = 'danger') {
    const toast = await this.toastController.create({
      message: message,
      duration: 2000,
      color,
      position: 'bottom'
    });
    await toast.present();
  }

  ngOnInit() {
  }
}