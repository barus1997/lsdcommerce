<?php 
/**
 * Manage Payment Method
 * 
 * Display Payment based on registered payment, status, and confirmation
 * Payment Settings : lsdcommerce_payment_option
 * 
 * @package LSDCommerce
 * @subpackage Payment
 * @since 1.0.0
 * 
 * Experimental ::
 * Sort payment by Drag
 * Custom Bank Method
 */
?>
<section id="payments">

  <div class="container columns col-gapless header">
    <div class="column col-5"><?php _e('Method', 'lsdcommerce' ); ?></div>
    <div class="column col-3"><?php _e('Enabled', 'lsdcommerce' ); ?></div>
    <div class="column col-2"><?php _e('Confirmation', 'lsdcommerce' ); ?></div>
    <div class="column col-2 text-right"><?php _e('Actions', 'lsdcommerce' ); ?></div>
  </div>

  <ul class="methods" id="draggable">
    <?php 
      global $lsdcommerce_payments;
      $payment_option = get_option( 'lsdcommerce_payment_option' );
    
      if( $lsdcommerce_payments ) {
        foreach( $lsdcommerce_payments as $key => $payment )
        { 
          if( class_exists( $payment ) ) :
            $payment = new $payment;
    ?>
            <li class="draggable" draggable="true">
              <div class="columns col-gapless">

                <!-- Display Payment Logo -->
                <div class="column col-5 method">
                  <?php 
                    // Custom Bank Payment
                    $pointer = isset( $payment_option[$payment->id]['alias'] ) ? esc_attr($payment_option[$payment->id]['alias']) : $payment->id; 
                    $logo = isset( $payment_option[$pointer]['logo'] ) ? esc_url( $payment_option[$pointer]['logo']) : $payment->logo;
                  ?>
                  <img src="<?php echo $logo; ?>" alt="<?php echo $payment->get_name(); ?>" style="height:33px;">
                </div>

                <!-- Display Status -->
                <div class="column col-3 lsdc-payment-change-status"><?php echo $payment->status(); ?></div>

                <!-- Display Confirmation Type -->
                <div class="column col-2 confirmation"> 
                  <?php if( $payment->confirmation() == 'manual' ) :  ?>
                    <span class="label label-secondary"><?php _e( 'Manual', 'lsdcommerce' ); ?></span>
                  <?php else: ?>
                    <span class="label label-success"><?php _e( 'Automatic', 'lsdcommerce' ); ?></span>
                  <?php endif; ?>
                </div>

                <!-- Display Payment Manage Button -->
                <div class="column col-2 text-right">
                  <button class="btn lsdc-payment-manage" id="<?php echo $payment->id; ?>"><?php _e( 'Manage', 'lsdcommerce' ); ?></button>
                </div>

              </div>
            </li>
            <!-- Calling Manage Bank -->
            <?php $payment->manage(); ?>
    <?php
        endif;
      }
    }
    ?>
  </ul>
  
</section>

<!-- Panel Editor -->
<div class="column pane">
    <div id="payment-editor" class="panel panel-style"></div>
</div>

<!-- Draggable Function -->
<script>
  const draggables = document.querySelectorAll('.draggable')
  const containers = document.querySelectorAll('.methods')


  draggables.forEach(draggable => {
    draggable.addEventListener('dragstart', ( event ) => {
      draggable.classList.add('dragging')

    })

    draggable.addEventListener('dragend', ( event ) => {
      draggable.classList.remove('dragging')
      // sending to reoder payment
    })
  })

  containers.forEach(container => {
    container.addEventListener('dragover', e => {
      e.preventDefault()
      
      const afterElement = getDragAfterElement(container, e.clientY)
      const draggable = document.querySelector('.dragging')
      if (afterElement == null) {
        container.appendChild(draggable)
      } else {
        container.insertBefore(draggable, afterElement)
      }
    })
  })

  function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.draggable:not(.dragging)')]

    return draggableElements.reduce((closest, child) => {
      const box = child.getBoundingClientRect()
      const offset = y - box.top - box.height / 2
      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child }
      } else {
        return closest
      }
    }, { offset: Number.NEGATIVE_INFINITY }).element
  }
</script>
