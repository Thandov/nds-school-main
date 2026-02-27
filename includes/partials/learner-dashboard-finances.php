<?php
/**
 * Learner Dashboard - Finances Tab
 */
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$learner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get financial data from payments table
$table_payments = $wpdb->prefix . 'nds_payments';

// Calculate outstanding balance (pending + overdue)
$outstanding_balance = $wpdb->get_var($wpdb->prepare("
    SELECT SUM(amount) 
    FROM {$table_payments} 
    WHERE student_id = %d AND status IN ('pending', 'overdue')
", $learner_id)) ?: 0;

// Calculate total paid
$total_paid = $wpdb->get_var($wpdb->prepare("
    SELECT SUM(amount) 
    FROM {$table_payments} 
    WHERE student_id = %d AND status = 'paid'
", $learner_id)) ?: 0;

// Get all payments for history
$all_payments = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$table_payments} 
    WHERE student_id = %d
    ORDER BY created_at DESC
", $learner_id), ARRAY_A) ?: [];

// Get pending payments for count display
$pending_payments = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$table_payments} 
    WHERE student_id = %d AND status IN ('pending', 'overdue')
    ORDER BY due_date ASC
", $learner_id), ARRAY_A) ?: [];
?>

<div class="space-y-6">
    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Outstanding Balance</p>
                    <p class="mt-2 text-2xl font-semibold text-red-600">R <?php echo number_format_i18n($outstanding_balance, 2); ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Paid</p>
                    <p class="mt-2 text-2xl font-semibold text-green-600">R <?php echo number_format_i18n($total_paid, 2); ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Payments</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-600"><?php echo count($pending_payments); ?></p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Payment History</h2>
            <button class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow-sm transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Record Payment
            </button>
        </div>
        <?php if (!empty($all_payments)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($all_payments as $payment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo esc_html($payment['description'] ?: ucfirst(str_replace('_', ' ', $payment['payment_type']))); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    R <?php echo number_format($payment['amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php 
                                        switch ($payment['status']) {
                                            case 'paid': echo 'bg-green-100 text-green-800'; break;
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'overdue': echo 'bg-red-100 text-red-800'; break;
                                            case 'cancelled': echo 'bg-gray-100 text-gray-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $payment['due_date'] ? date('M j, Y', strtotime($payment['due_date'])) : 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $payment['paid_date'] ? date('M j, Y', strtotime($payment['paid_date'])) : 'N/A'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-money-bill-wave text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">No Payment Records</h3>
            <p class="text-gray-600">No payments have been recorded for this student yet.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Fee Structure -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Fee Structure</h2>
        <div class="text-center py-12">
            <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Fee Management</h3>
            <p class="text-gray-600">Configure course fees and payment plans here.</p>
        </div>
    </div>
</div>
