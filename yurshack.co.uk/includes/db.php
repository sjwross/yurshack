<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = AppConfig::requireValue('POSTGRES_DSN');
        $user = AppConfig::get('POSTGRES_USER', '');
        $pass = AppConfig::get('POSTGRES_PASS', '');

        self::$pdo = new PDO($dsn, $user ?: null, $pass ?: null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }

    /**
     * @param array{
     *   customer_name:string,
     *   customer_email:string,
     *   customer_phone?:?string,
     *   business_name?:?string,
     *   website_domain?:?string,
     *   domain_arrangement:string,
     *   initial_term?:?string,
     *   payment_method?:?string,
     *   special_scope?:?string,
     *   services:array<int, array<string, mixed>>,
     *   estimated_setup_gbp?:?float,
     *   estimated_monthly_gbp?:?float,
     *   estimated_yearly_gbp?:?float,
     *   notes?:?string,
     *   ip_address?:?string,
     *   user_agent?:?string
     * } $order
     */
    public static function insertOrder(array $order): int
    {
        $sql = <<<'SQL'
INSERT INTO orders (
    customer_name, customer_email, customer_phone, business_name,
    website_domain, domain_arrangement, initial_term, payment_method,
    special_scope, services, estimated_setup_gbp, estimated_monthly_gbp,
    estimated_yearly_gbp, notes, ip_address, user_agent
) VALUES (
    :customer_name, :customer_email, :customer_phone, :business_name,
    :website_domain, :domain_arrangement, :initial_term, :payment_method,
    :special_scope, CAST(:services AS JSONB), :estimated_setup_gbp, :estimated_monthly_gbp,
    :estimated_yearly_gbp, :notes, :ip_address, :user_agent
)
RETURNING id
SQL;

        $stmt = self::pdo()->prepare($sql);
        $stmt->execute([
            ':customer_name' => $order['customer_name'],
            ':customer_email' => $order['customer_email'],
            ':customer_phone' => $order['customer_phone'] ?? null,
            ':business_name' => $order['business_name'] ?? null,
            ':website_domain' => $order['website_domain'] ?? null,
            ':domain_arrangement' => $order['domain_arrangement'],
            ':initial_term' => $order['initial_term'] ?? null,
            ':payment_method' => $order['payment_method'] ?? null,
            ':special_scope' => $order['special_scope'] ?? null,
            ':services' => json_encode($order['services'], JSON_THROW_ON_ERROR),
            ':estimated_setup_gbp' => $order['estimated_setup_gbp'] ?? null,
            ':estimated_monthly_gbp' => $order['estimated_monthly_gbp'] ?? null,
            ':estimated_yearly_gbp' => $order['estimated_yearly_gbp'] ?? null,
            ':notes' => $order['notes'] ?? null,
            ':ip_address' => $order['ip_address'] ?? null,
            ':user_agent' => $order['user_agent'] ?? null,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function queryOrders(
        ?string $q = null,
        ?string $status = null,
        ?string $from = null,
        ?string $to = null,
        int $limit = 100
    ): array {
        $where = [];
        $params = [];

        if ($q !== null && $q !== '') {
            $where[] = '(customer_name ILIKE :q OR customer_email ILIKE :q OR COALESCE(business_name, \'\') ILIKE :q OR COALESCE(website_domain, \'\') ILIKE :q OR CAST(id AS TEXT) = :qid)';
            $params[':q'] = '%' . $q . '%';
            $params[':qid'] = $q;
        }
        if ($status !== null && $status !== '' && $status !== 'all') {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }
        if ($from !== null && $from !== '') {
            $where[] = 'created_at >= :from::timestamptz';
            $params[':from'] = $from . ' 00:00:00';
        }
        if ($to !== null && $to !== '') {
            $where[] = 'created_at < (:to::date + INTERVAL \'1 day\')';
            $params[':to'] = $to;
        }

        $sql = 'SELECT * FROM orders';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ' . max(1, min(500, $limit));

        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateOrderStatus(int $id, string $status): void
    {
        $allowed = ['new', 'reviewing', 'accepted', 'declined', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Invalid status');
        }
        $stmt = self::pdo()->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
