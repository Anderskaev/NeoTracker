import { Component, inject, OnInit, OnDestroy } from '@angular/core';
import { RouterLink, Router } from '@angular/router';
import { DataService } from '../../services/data.service';
import { MessageService } from 'primeng/api';
import { TelegramService } from '../../services/telegram.service';
import { AccordionModule } from 'primeng/accordion';
import { InputSwitchModule } from 'primeng/inputswitch';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-buy-page',
  standalone: true,
  imports: [RouterLink, AccordionModule, InputSwitchModule, FormsModule],
  templateUrl: './buy-page.component.html',
  styleUrl: './buy-page.component.scss'
})
export class BuyPageComponent implements OnInit, OnDestroy {

  router = inject(Router);
  dataService = inject(DataService);
  messageService = inject(MessageService);
  telegram = inject(TelegramService);


  accept = false;

  price = 1;
  title = "NeoTracker";
  descr = "Интерактивная история по мотивам вселенной киберпанк. Сюжетная линия зависит от выбора игрока.";
  label = "Цена";
  currency = "⭐️";

  ngOnInit() {
    this.telegram.onEvent('invoiceClosed', this.invoiceClosed.bind(this));
  }

  ngOnDestroy() {
    this.telegram.offEvent('invoiceClosed', this.invoiceClosed.bind(this));
  }

  invoiceClosed(data: any){
  if(data.status === 'paid') {
    this.messageService.add({ severity: 'success', summary: "Успешно", detail: "Покупка оплачена"});
    this.router.navigate(['/main']);
  }
  if(data.status === 'pending') {
    this.messageService.add({ severity: 'success', summary: "Успешно", detail: "Оплата в обработке"});
    this.router.navigate(['/main']);
  }
  //paid
  //pending

  }

  async showPopup() {
    const result = await this.telegram.showPopup({
      title: 'Внимание',
      message: 'Для продолжения Вам необходимо принять все условия',
      buttons: [
        { id: 'yes', type: 'default', text: 'Ок' },
      ]
    });

  }

  buy() {
    if(!this.accept) {
      this.showPopup();
      return;
    }


    this.dataService.botPay(this.title, this.descr, this.label, this.price, 'buy_story').subscribe({
      next: (data)=> {
        let response = "";
        if(data.result) {
          this.telegram.openInvoice(data.result);
        } else {
          response = "Ошибка при получении ссылки на платёж";
          this.messageService.add({ severity: 'error', summary: "Ошибка", detail: response});
        }
      }
    });
  }

}
