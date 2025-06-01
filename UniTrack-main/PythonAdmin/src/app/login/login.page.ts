import { Component, OnInit } from '@angular/core';
import { UserService } from '../services/user.service';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit {
  correo: string = ''; 
  contrasena: string = '';
  showPassword: boolean = false;
  isSubmitting = false;

  constructor(
    private userService: UserService, 
    private router: Router,
    private toastController: ToastController
  ) { }

  async presentToast(message: string, color: string = 'danger') {
    const toast = await this.toastController.create({
      message,
      duration: 2000,
      color,
      position: 'bottom'
    });
    await toast.present();
  }

  login() {
    if (this.isSubmitting) return;
    this.isSubmitting = true;

    // Validación de campos vacíos
    if (!this.correo.trim() || !this.contrasena.trim()) {
      this.presentToast('Todos los campos son obligatorios');
      this.isSubmitting = false;
      return;
    }

    // Validación de formato de correo institucional
    const correoRegex = /^[a-zA-Z0-9._%+-]+@ucvvirtual\.edu\.pe$/;
    if (!correoRegex.test(this.correo.trim())) {
      this.presentToast('Debe ingresar un correo institucional válido (@ucvvirtual.edu.pe)');
      this.isSubmitting = false;
      return;
    }

    // Validación de longitud de correo
    if (this.correo.length > 100) {
      this.presentToast('El correo no debe exceder los 100 caracteres');
      this.isSubmitting = false;
      return;
    }

    // Validación de contraseña
    if (this.contrasena.length < 6) {
      this.presentToast('La contraseña debe tener al menos 6 caracteres');
      this.isSubmitting = false;
      return;
    }

    if (this.contrasena.length > 50) {
      this.presentToast('La contraseña no debe exceder los 50 caracteres');
      this.isSubmitting = false;
      return;
    }

    // Intento de login
    this.userService.loginUser(this.correo.trim(), this.contrasena).subscribe({
      next: (response) => {
        if (response.error) {
          this.handleLoginError(response.message);
        } else {
          this.handleLoginSuccess(response);
        }
      },
      error: (error) => {
        this.handleLoginError(error.message || 'Error de conexión con el servidor');
      }
    });
  }

  private handleLoginSuccess(response: any) {
    this.userService.setCurrentUser(response);
    this.presentToast('¡Bienvenido!', 'success');
    this.router.navigate(['/home']);
    this.isSubmitting = false;
  }

  private handleLoginError(errorMessage: string) {
    const errorMessages: {[key: string]: string} = {
      'User not found': 'Usuario no registrado',
      'Invalid credentials': 'Correo o contraseña incorrectos',
      'Account disabled': 'Cuenta desactivada, contacte al administrador',
      'Too many attempts': 'Demasiados intentos fallidos, intente más tarde'
    };

    const friendlyMessage = errorMessages[errorMessage] || errorMessage;
    this.presentToast(friendlyMessage);
    this.isSubmitting = false;
  }

  ngOnInit() { 
    this.resetForm();
  }

  ionViewWillEnter() {
    this.resetForm();
  }
  
  togglePassword() {
    this.showPassword = !this.showPassword;
  }

  private resetForm() {
    this.correo = '';
    this.contrasena = '';
    localStorage.removeItem('correo');
    localStorage.removeItem('contrasena');
  }
}