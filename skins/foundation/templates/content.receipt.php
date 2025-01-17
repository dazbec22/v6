{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<h2>{$LANG.orders.order_number}: {$SUM.{$CONFIG.oid_col}|default:$SUM.order_id}</h2>
<div class="order_status marg-top">{$LANG.orders.title_order_status}: <span class="order_status_{$SUM.status}">{$SUM.order_status}</span></div>
<div><strong>{$LANG.basket.order_date}:</strong> {$SUM.order_date_formatted}</div>
<hr>
<h3>{$LANG.basket.customer_info}</h3>
<div class="row">
   <div class="small-6 columns">
      <strong>{$LANG.address.billing_address}</strong><br>
      {$SUM.title} {$SUM.first_name|capitalize} {$SUM.last_name|capitalize}<br>
      {if $SUM.company_name}{$SUM.company_name}<br>{/if}
      {$SUM.line1|capitalize}<br>
      {if $SUM.line2|capitalize}{$SUM.line2|capitalize}<br>{/if}
      {$SUM.town|upper}<br>
      {if !empty($SUM.state)}{$SUM.state|upper}, {/if}{$SUM.postcode}{if $CONFIG['store_country_name']!==$SUM['country']}<br>
      {$SUM.country}{/if}
      {if !empty($SUM.w3w)}<div class="w3w">///<a href="https://what3words.com/{$SUM.w3w}">{$SUM.w3w}</a></div>{/if}
   </div>
   <div class="small-6 columns">
      <strong>{$LANG.address.delivery_address}</strong><br>
      {$SUM.title_d} {$SUM.first_name_d} {$SUM.last_name_d}<br>
      {if $SUM.company_name_d}{$SUM.company_name_d}<br>{/if}
      {$SUM.line1_d|capitalize}<br>
      {if $SUM.line2_d|capitalize}{$SUM.line2_d|capitalize}<br>{/if}
      {$SUM.town_d|upper}<br>
      {if !empty($SUM.state_d)}{$SUM.state_d|upper}, {/if}{$SUM.postcode_d}{if $CONFIG['store_country_name']!==$SUM['country_d']}<br>
      {$SUM.country_d}{/if}
      {if !empty($SUM.w3w_d)}<div class="w3w">///<a href="https://what3words.com/{$SUM.w3w_d}">{$SUM.w3w_d}</a></div>{/if}
   </div>
</div>
{if $DELIVERY}
<hr>
<h4>{$LANG.common.delivery}</h4>
{if !empty($DELIVERY.date)}
<div class="row">
  <div class="small-6 medium-3 columns">{$LANG.orders.shipping_date}:</div>
  <div class="small-6 medium-9 columns">{$DELIVERY.date}</div>
</div>
{/if}
{if !empty($DELIVERY.method)}
<div class="row">
  <div class="small-6 medium-3 columns">{$LANG.catalogue.delivery_method}:</div>
  <div class="small-6 medium-9 columns">{str_replace('_',' ',$DELIVERY.method)}{if !empty($DELIVERY.product)} ({str_replace('_',' ',$DELIVERY.product)}){/if}</div>
</div>
{/if}
{if !empty($DELIVERY.url)}
<div class="row">
  <div class="small-6 medium-3 columns">{$LANG.orders.shipping_tracking}:</div>
  <div class="small-6 medium-9 columns"><a href="{$DELIVERY.url}" target="_blank">{$DELIVERY.tracking}</a></div>
</div>
{elseif !empty($DELIVERY.tracking)}
<div class="row">
  <div class="small-6 medium-3 columns">{$LANG.orders.shipping_tracking}:</div>
  <div class="small-6 medium-9 columns">{$DELIVERY.tracking}</div>
</div>
{/if}
{/if}
<hr>
<h3>{$LANG.basket.order_summary}</h3>
<table class="expand">
   <thead>
      <tr>
         <th>{$LANG.common.product}</th>
         <th class="text-center">{$LANG.catalogue.price_each}</th>
         <th class="text-center">{$LANG.common.quantity}</th>
         <th class="text-right">{$LANG.common.price}</th>
      </tr>
   </thead>
   <tbody>
      {foreach from=$ITEMS item=item}
      <tr>
         <td>
            {$item.name}{if !empty($item.product_code)} ({$item.product_code}){/if}
            {if !empty($item.options)}<br>
            <small>{foreach from=$item.options item=option}{$option}<br>{/foreach}</small>
            {/if}
         </td>
         <td class="text-center">{$item.price}</td>
         <td class="text-center">{$item.quantity}</td>
         <td class="text-right">{$item.price_total}</td>
      </tr>
   </tbody>
   {/foreach}
   <tfoot>
      <tr>
         <td colspan="2"></td>
         <td>{$LANG.basket.total_sub}</td>
         <td class="text-right">{$SUM.subtotal}</td>
      </tr>
      <tr>
         <td colspan="2"></td>
         <td>{if !empty($SUM.ship_method)}{str_replace('_',' ',$SUM.ship_method)}{if !empty($SUM.ship_product)} ({$SUM.ship_product}){/if}{else}{$LANG.basket.shipping}{/if}</td>
         <td class="text-right">{$SUM.shipping}</td>
      </tr>
      {foreach from=$TAXES item=tax}
      <tr>
         <td colspan="2"></td>
         <td>{$tax.name}</td>
         <td class="text-right">{$tax.value}</td>
      </tr>
      {/foreach}
      {if $DISCOUNT}
      <tr>
         <td colspan="2"></td>
         <td>{$LANG.basket.total_discount}</td>
         <td class="text-right">{$SUM.discount}</td>
      </tr>
      {/if}
      <tr>
         <td colspan="2"></td>
         <td>{$LANG.basket.total_grand}</td>
         <td class="text-right">{$SUM.total}</td>
      </tr>
   </tfoot>
</table>
{if !empty($SUM.note_to_customer)}
<blockquote>{$SUM.note_to_customer}</blockquote>
{/if}
{if !empty($SUM.customer_comments)}
<h3>{$LANG.common.comments}</h3>
<p>&quot;{$SUM.customer_comments}&quot;</p>
{/if}
<p><a href="{$STORE_URL}/index.php?_a=receipt&cart_order_id={$SUM.cart_order_id}{if !$IS_USER}&email={$SUM.email}{/if}" target="_blank"><svg class="icon"><use xlink:href="#icon-print"></use></svg> {$LANG.confirm.print}</a></p>
{foreach from=$AFFILIATES item=affiliate}{$affiliate}{/foreach}