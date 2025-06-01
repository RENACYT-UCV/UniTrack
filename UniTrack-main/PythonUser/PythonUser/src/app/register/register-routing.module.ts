import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { RegisterPage } from './register.page';  // Mantén 'RegisterPage' ya que el archivo se llama register.page.ts

const routes: Routes = [
  {
    path: '',
    component: RegisterPage  // Usamos 'RegisterPage' porque el archivo se llama 'register.page.ts'
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class RegisterPageRoutingModule {}
