<?php
/**
 * Vista: Resultado del tracking
 * Variables disponibles: $data, $tracking_number
 */
?>

<div class="bt__tracking-wrapper">

    <div class="bt__header">
        <h2>Tracking</h2>
        <a href="<?php echo esc_url(remove_query_arg('boxes_tracking_number')); ?>" class="bt__button">NUEVA BÚSQUEDA</a>
    </div>

    <div class="bt__summary">
        <p><strong>Código:</strong> (<?php echo esc_html(strtoupper($data['carrier'])); ?>) <?php echo esc_html($data['tracking_number']); ?></p>

        <?php if (!empty($data['estimated_delivery'])): ?>
            <p><strong>La entrega se ha realizado el:</strong> <?php echo esc_html(date('d-m-Y H:i', strtotime($data['estimated_delivery']))); ?></p>
        <?php endif; ?>

        <?php if (!empty($data['signed_by'])): ?>
            <p><strong>Firmado por:</strong> <?php echo esc_html($data['signed_by']); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($data['events']) && is_array($data['events'])): ?>
        <h3>Estado del pedido</h3>
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
                        <td><?php echo esc_html($event['timestamp']); ?></td>
                        <td><?php echo esc_html($event['status'] . (!empty($event['location']) ? ' - ' . $event['location'] : '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
