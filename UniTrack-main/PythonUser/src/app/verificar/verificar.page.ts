import { Component, OnInit } from '@angular/core';
import { UserService } from 'src/app/services/user.service';
import { NavController } from '@ionic/angular';
import { EnvioCorreoService } from '../services/envio-correo.service';
@Component({
  selector: 'app-verificar',
  templateUrl: './verificar.page.html',
  styleUrls: ['./verificar.page.scss'],
})
export class VerificarPage implements OnInit {
  verificationCode: number | null= null ;
  constructor(private userService: EnvioCorreoService, private navCtrl: NavController) { }

  ngOnInit() {
  }
  verifyCode() {
    if(this.verificationCode){
      console.log(this.verificationCode); 
      this.userService.verifyVerificationCode(this.verificationCode).subscribe(
        response => {
          this.navCtrl.navigateForward('/contrasena');
        },
        error => {
          console.error(error);
          // Manejo de errores
        }
      );
    }
   
  }
}
