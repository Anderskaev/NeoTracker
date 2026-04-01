import { Injectable, inject } from '@angular/core';
import { TelegramService } from './telegram.service';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable, of } from 'rxjs';

const HOST = environment.apiUrl;

@Injectable({
  providedIn: 'root'
})

export class DataService {

  telegram = inject(TelegramService);
  http = inject(HttpClient);
  initData = this.telegram.InitData;

  constructor() { }

  getStats(period: string): Observable<any> {
    let initData = this.telegram.InitData;
    return this.http.get<any>(HOST+'/api/stats/'+period+'?initData='+encodeURIComponent(initData));
  }

  getMissionReq(): Observable<any> {
    let initData = this.telegram.InitData;
    return this.http.get<any>(HOST+'/api/mission/req?initData='+encodeURIComponent(initData));
  }

  addStats(stats: any): Observable<any> {
    let initData = this.telegram.InitData;
    let data = {
      'initData': initData,
      'stats': stats
    };
    return this.http.post<any>(HOST+'/api/stats', data);
  }

  setStats(stats: any): Observable<any> {
    let initData = this.telegram.InitData;
    let data = {
      'initData': initData,
      'stats': stats
    };
    return this.http.patch<any>(HOST+'/api/stats', data);
  }

  getGraph(){
    let initData = this.telegram.InitData;
    return this.http.get<any>(HOST+'/api/graph?initData='+encodeURIComponent(initData));
  }

  sendBot(name: string, cmd: string): Observable<any> {
    let initData = this.telegram.InitData;

    const update = {
      'initData': initData,
      'message': {
        /*from: {
          id: 6830621933 // ID пользователя (должен браться из initData!)
        },
        chat: {
          id: 6830621933 // ID чата
        },*/
        text: cmd,
        bot_name: name, //alexns, tracker, maks
        date: Math.floor(Date.now() / 1000)
      }
    };

    return this.http.post<any>(HOST+'/api/bot/command', update);
  }

  botPay(title: string, descr: string, label: string, price: number, payload: string) {
    let initData = this.telegram.InitData;

    const data = {
      'initData': initData,
      'payload': {
        title: title,
        descr: descr,
        label: label,
        payload: payload,
        price: price
      }
    };

    return this.http.post<any>(HOST+'/api/bot/pay', data);
  }

  setSettings(settings: any): Observable<any> {
    let initData = this.telegram.InitData;

    let data = {
      'initData': initData,
      'settings': settings
    };
    return this.http.patch<any>(HOST+'/api/settings', data);
  }

  getSettings(){
    let initData = this.telegram.InitData;
    return this.http.get<any>(HOST+'/api/settings?initData='+encodeURIComponent(initData));
  }

  getProfile(){
    let initData = this.telegram.InitData;
    return this.http.get<any>(HOST+'/api/profile?initData='+encodeURIComponent(initData));
  }


  setProfile(profile: any): Observable<any> {
    let initData = this.telegram.InitData;

    let data = {
      'initData': initData,
      'profile': profile
    };
    return this.http.patch<any>(HOST+'/api/profile', data);
  }

  checkPremium(){
    let initData = this.telegram.InitData;
    return this.http.get<any>(HOST+'/api/check_premium?initData='+encodeURIComponent(initData));
  }

}
