import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { CorreoPage } from './correo.page';  // Asegúrate de que el componente se importe correctamente

const routes: Routes = [
  {
    path: '',
    component: CorreoPage  // Asegúrate de que esta ruta sea correcta
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class CorreoPageRoutingModule {}
