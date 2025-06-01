import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ContrasenaPage } from './contrasena.page';  // Importa correctamente el componente

const routes: Routes = [
  {
    path: '',
    component: ContrasenaPage  // Asegúrate de que esta ruta sea correcta
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ContrasenaPageRoutingModule {}
