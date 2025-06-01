import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';
import { RegisterPage } from './register.page';  // 'RegisterPage' mantiene la referencia al archivo original
import { RegisterPageRoutingModule } from './register-routing.module';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    RegisterPageRoutingModule
  ],
  declarations: [RegisterPage]  // Usamos 'RegisterPage' porque el archivo es 'register.page.ts'
})
export class RegisterPageModule {}
