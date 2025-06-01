import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { NavController } from '@ionic/angular';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = environment.apiUrl; // URL de tu API
  public currentUser: any = null; 

  constructor(private http: HttpClient, private navCtrl: NavController) { }

  // Crear un nuevo usuario (ya existente)
  createUser(nombres: string, apellidos: string, correo: string, codigo_estudiante: string, contrasena: string, correoA: string, carrera: string, ciclo: string, edad: string, sexo: string): Observable<any> {
    if (!correo.endsWith('@ucvvirtual.edu.pe')) {
      return throwError('El correo debe ser de la universidad');
    }
    const body = { nombres, apellidos, correo, codigo_estudiante, contrasena, correoA, carrera, ciclo, edad, sexo };
    return this.http.post(this.apiUrl, body);
  }

  // Función para registrar al usuario
  registerUser(
    nombres: string,
    apellidos: string,
    correo: string,
    codigo_estudiante: string,
    correoA: string,
    carrera: string,
    ciclo: string,
    edad: string,
    sexo: string,
    contrasena: string
  ): Observable<any> {
    if (!correo.endsWith('@ucvvirtual.edu.pe')) {
      return throwError('El correo debe ser de la universidad');
    }
    const body = { nombres, apellidos, correo, codigo_estudiante, correoA, carrera, ciclo, edad, sexo, contrasena };
    return this.http.post(`${this.apiUrl}/register`, body);  // Usamos la URL del backend para registrar
  }

  // Actualizar un usuario
  updateUser(id: number, nombres: string, apellidos: string, correo: string, codigo_estudiante: string): Observable<any> {
    const body = { action: 'update', id, nombres, apellidos, correo, codigo_estudiante };
    return this.http.post(`${this.apiUrl}`, body);
  }

  // Eliminar un usuario
  deleteUser(id: number): Observable<any> {
    const body = { action: 'delete', id };
    return this.http.post(`${this.apiUrl}`, body);
  }

  // Iniciar sesión de usuario
  loginUser(correo: string, contrasena: string): Observable<any> {
    const body = { action: 'login', correo, contrasena };
    return this.http.post(`${this.apiUrl}`, body);
  }

  // Establecer el usuario actual
  setCurrentUser(user: any) {
    this.currentUser = user;
  }

  // Obtener el usuario actual
  getCurrentUser() {
    if (!this.currentUser) {
      const storedUser = localStorage.getItem('currentUser');
      this.currentUser = storedUser ? JSON.parse(storedUser) : null;
    }
    return this.currentUser;
  }

  // Obtener historial de usuario
  gethistorial(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}?action=historial&idUsuario=${id}`);
  }
}
