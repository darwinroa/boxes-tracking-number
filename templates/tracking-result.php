<?php
/**
 * Vista: Resultado del tracking
 * Variables disponibles: $data, $tracking_number
 */
?>

<div class="bt__tracking-wrapper">
    <h3 class="bt__title_response">Informaciones envío</h3>

    <div class="bt__summary">
        <p><span>Código:</span> (<?php echo esc_html(strtoupper($data['carrier'])); ?>) <?php echo esc_html($data['tracking_number']); ?></p>

        <?php if (!empty($data['estimated_delivery'])): ?>
            <p><span>La entrega se ha realizado el:</span> <?php echo esc_html(date('d-m-Y H:i', strtotime($data['estimated_delivery']))); ?></p>
        <?php endif; ?>

        <?php if (!empty($data['signed_by'])): ?>
            <p><span>Firmado por:</span> <?php echo esc_html($data['signed_by']); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($data['events']) && is_array($data['events'])): ?>
        <h3 class="bt__title_response">Estado del pedido</h3>
        <table class="bt__events-table">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>DESCRIPCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['events'] as $event): ?>
                    <tr>
                        <td>
                            <?php
                                $formatted_date = date('Y-m-d H:i:s', strtotime($event['timestamp']));
                                echo esc_html($formatted_date);
                            ?>
                        </td>
                        <td><?php echo esc_html($event['status'] . (!empty($event['location']) ? ' - ' . $event['location'] : '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
