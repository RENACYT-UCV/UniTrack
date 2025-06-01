import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { FlaskApiService } from '../services/flask-api.services.service';
import { UserService } from '../services/user.service';
import { AlertController } from '@ionic/angular';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-qr',
  templateUrl: './qr.page.html',
  styleUrls: ['./qr.page.scss']
})
export class QrPage implements OnInit {
  qrImageUrl: string = ''; 
  nombrecompleto: string = ''; 
  currentUser: any; 

  constructor(
    private route: ActivatedRoute, 
    private flaskService: FlaskApiService, 
    private user: UserService, 
    private alertController: AlertController, 
    private httpClient: HttpClient
  ) {}

  ngOnInit() {
    this.route.queryParams.subscribe(params => {
      if (params['qrImageUrl']) {
        this.qrImageUrl = params['qrImageUrl'];
        console.log('Received QR Image URL:', this.qrImageUrl); 
      }
    });

    this.currentUser = this.user.getCurrentUser();
    if (this.currentUser && this.currentUser.user && this.currentUser.user.nombres && this.currentUser.user.apellidos) {
      this.nombrecompleto = `${this.currentUser.user.nombres} ${this.currentUser.user.apellidos}`;
    } else {
      console.error('Usuario no encontrado o datos incompletos');
    }
  }
}
