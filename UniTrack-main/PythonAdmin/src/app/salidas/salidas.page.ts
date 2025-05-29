import { Component, OnInit } from '@angular/core';
import { FlaskService } from '../services/flask.service';
import { UserService } from '../services/user.service';
import { Reporte } from '../services/reporte';

@Component({
  selector: 'app-salidas',
  templateUrl: './salidas.page.html',
  styleUrls: ['./salidas.page.scss'],
})
export class SalidasPage implements OnInit {

  reportes: Reporte[] = []; // Utiliza la interfaz Reporte para definir el tipo de reportes
  verificationResult: string = '';
  nombrecompleto: string = ''; 
  

  constructor(private userService: UserService, private flaskservice: FlaskService) {
  
  }

  Reportes(): void {
    this.userService.getReportesSalidas().subscribe(
      data => {
        this.reportes = data;
      },
      error => {
        console.error('Error al obtener los reportes', error);
      }
    );
  }
  ngOnInit() {
    this.Reportes();

    const currentUser = this.userService.getCurrentUser();
    if (currentUser) {
      this.nombrecompleto = `${currentUser.nombres} ${currentUser.apellidos}`;
    }
  }
}
