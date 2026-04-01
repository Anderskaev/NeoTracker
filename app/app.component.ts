import { Component, inject } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { TelegramService } from './services/telegram.service';
import { ToastModule } from 'primeng/toast';
import { ConfirmDialogModule  } from 'primeng/confirmdialog';
import { Router } from '@angular/router';
import { CheckIcon } from 'primeng/icons/check';
import { ExclamationTriangleIcon } from 'primeng/icons/exclamationtriangle';
import { InfoCircleIcon } from 'primeng/icons/infocircle';
import { TimesCircleIcon } from 'primeng/icons/timescircle';
import { CommonModule } from '@angular/common';



@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, ToastModule, ConfirmDialogModule, CheckIcon, ExclamationTriangleIcon, InfoCircleIcon, TimesCircleIcon, CommonModule ],
  template: `<p-toast [life]="2000" position="top-center" width="80%"/>
    <p-toast position="center" key="cnt">
        <ng-template let-message pTemplate="message">

        <span *ngIf="message.icon" class="p-toast-message-icon" style="color: #00ffe0"></span>
        <span *ngIf="!message.icon" class="p-toast-message-icon" style="color: #00ffe0" [attr.aria-hidden]="true" [attr.data-pc-section]="'icon'">
            @switch (message.severity) {
                @case ('success') {
                    <CheckIcon [attr.aria-hidden]="true" [attr.data-pc-section]="'icon'" />
                }
                @case ('info') {
                    <InfoCircleIcon [attr.aria-hidden]="true" [attr.data-pc-section]="'icon'" />
                }
                @case ('error') {
                    <TimesCircleIcon [attr.aria-hidden]="true" [attr.data-pc-section]="'icon'" />
                }
                @case ('warn') {
                    <ExclamationTriangleIcon [attr.aria-hidden]="true" [attr.data-pc-section]="'icon'" />
                }
                @default {
                    <InfoCircleIcon [attr.aria-hidden]="true" [attr.data-pc-section]="'icon'" />
                }
            }
        </span>
        <div class="p-toast-message-text" [attr.data-pc-section]="'text'">
            <div class="p-toast-summary" [attr.data-pc-section]="'summary'">
                {{ message.summary }}
            </div>
            <p class="p-toast-detail" innerHtml="{{message.detail}}"></p>
        </div>

        </ng-template>
    </p-toast>
    <p-confirmDialog/><router-outlet/>`
})

export class AppComponent {
  telegram = inject(TelegramService);
  router = inject(Router);

  constructor(){
    this.telegram.ready();
    this.telegram.expand();
    //this.telegram.fullscreen();
    this.router.navigateByUrl(this.router.url, { replaceUrl: true });
   }

}
