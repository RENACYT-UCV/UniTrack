import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private router: Router,
    private http: HttpClient
  ) {}

  canActivate(): Observable<boolean> {
  return this.http.get<any>(
    `${environment.apiUrl}?action=checkSession`,
  
  ).pipe(
    map(response => response.active),
    catchError(err => {
      this.router.navigate(['/login']);
      return of(false);
    })
  );
}
}