import { Injectable } from '@angular/core';
import axios from 'axios';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class FlaskService {

  private baseUrl = 'http://localhost:5000';

  constructor(private http: HttpClient) {}

  async verifyQR() {
    try {
      const response = await axios.post(`${this.baseUrl}/verify_qr`);
      return response.data;
    } catch (error) {
      console.error('Error verificando QR:', error);
      throw error;
    }
  }
 

}
