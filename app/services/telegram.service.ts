import { DOCUMENT } from '@angular/common';
import { Inject, Injectable } from '@angular/core';
import { environment } from '../../environments/environment';

// интерфейс для функционала кнопок
interface TgButton {
  show(): void;
  hide(): void;
  setText(text: string): void;
  onClick(fn: Function): void;
  offClick(fn: Function): void;
  enable(): void;
  disable(): void;
}

export interface TgUser {
  id: number;
  first_name: string;
  last_name: string;
  username: string;
}

export interface TelegramWebAppPopupButton {
  id: string;
  type?: 'default' | 'ok' | 'close' | 'cancel' | 'destructive';
  text: string;
}

export interface TgData {
  query_id: string;
  user: TgUser;
}

@Injectable({
  providedIn: 'root'
})


export class TelegramService {
  private window;
  tg;
  //iData;

  constructor(@Inject(DOCUMENT) private _document: any) {
    this.window = this._document.defaultView;
    this.tg = this.window.Telegram.WebApp;
  }

  get MainButton(): TgButton {
    return this.tg.MainButton;
  }

  get BackButton(): TgButton {
    return this.tg.BackButton;
  }

  get InitData(): string {
    if (environment.production) {
      return this.tg.initData;
    } else {
      return "query_id=AAHtBCMXAwAAAO0EIxeGZfVT&user=%7B%22id%22%3A6830621933%2C%22first_name%22%3A%22Vlad%22%2C%22last_name%22%3A%22Anderskaev%22%2C%22username%22%3A%22Anderskaev%22%2C%22language_code%22%3A%22en%22%2C%22is_premium%22%3Atrue%2C%22allows_write_to_pm%22%3Atrue%7D&auth_date=1730543500&hash=c5c6e09de644e2d8e9a5005a3707a7512d029388d8d0b42023818c99c33f32df";
    }

  }

  get InitDataUnsafe(): TgData {
    return this.tg.initDataUnsafe;
  }

  openInvoice(url: string) {
    this.tg.openInvoice(url);
  }

  onEvent(eventType: string, callback: (data?: any) => void) {
    this.tg.onEvent(eventType, callback);
  }

  offEvent(eventType: string, callback: (data?: any) => void) {
    this.tg.onEvent(eventType, callback);
  }

  showPopup(params: {
    title: string;
    message: string;
    buttons?: TelegramWebAppPopupButton[];
  }): Promise<string> {
    return new Promise((resolve) => {
      if (!this.tg) {
        console.warn('Telegram WebApp not available');
        return resolve('cancel');
      }

      this.tg.showPopup(params, (buttonId: string) => {
        resolve(buttonId);
      });
    });
  }

  ready() {
    this.tg.ready();
  }

  expand() {
    this.tg.expand();
  }

  close() {
    this.tg.close();
  }

  fullscreen() {
    this.tg.requestFullscreen()
  }

  getItem(key: any) {
    return this.tg.DeviceStorage.getItem(key);
  }

  setItem(key: any, value: any) {
    return this.tg.DeviceStorage.setItem(key, value);
  }

  version() {
    return this.tg.version;
  }
  //Оставлю тут на крайний
  //npm i ctypto-js --save
  //Но тут глюк - это же фронтэнд
  /*validateInitData(initData, botToken) {
    const urlSearchParams = new URLSearchParams(initData);
    const data = Object.fromEntries(urlSearchParams);

    const checkString = Object.keys(data)
      .filter(key => key !== 'hash')
      .map(key => `${key}=${data[key]}`)
      .sort()
      .join('\n')

    const secretKey = Crypto.createHmac('sha256','WebAppData')
      .update(botToken)
      .digest()

    const signature = Crypto.createHmac('sha256', secretKey)
      .update(checkString)
      .digest('hex');

      return data['hash'] === signature;
  }*/

}
