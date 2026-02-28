
-- 1. Организации
CREATE TABLE organizations (
                               org_id          VARCHAR(20) NOT NULL PRIMARY KEY,
                               name            VARCHAR(255) NOT NULL,
                               inn             VARCHAR(12),
                               address         VARCHAR(500),
                               phone           VARCHAR(20),
                               is_supplier     TINYINT(1) NOT NULL DEFAULT 0,
                               is_customer     TINYINT(1) NOT NULL DEFAULT 0,
                               created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Материалы
CREATE TABLE materials (
                           material_id     VARCHAR(20) NOT NULL PRIMARY KEY,
                           name            VARCHAR(255) NOT NULL,
                           code            VARCHAR(20) NOT NULL UNIQUE,
                           unit            VARCHAR(10) NOT NULL,
                           current_price   DECIMAL(10,2) NOT NULL,
                           created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                           INDEX idx_materials_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. История цен
CREATE TABLE material_price_history (
                                        price_id        INT AUTO_INCREMENT PRIMARY KEY,
                                        material_id     VARCHAR(20) NOT NULL,
                                        price           DECIMAL(10,2) NOT NULL,
                                        effective_date  DATE NOT NULL,
                                        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                                        CONSTRAINT fk_price_material
                                            FOREIGN KEY (material_id) REFERENCES materials(material_id)
                                                ON DELETE RESTRICT ON UPDATE CASCADE,

                                        UNIQUE KEY uk_material_date (material_id, effective_date),
                                        INDEX idx_price_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Готовая продукция
CREATE TABLE products (
                          product_id      VARCHAR(20) NOT NULL PRIMARY KEY,
                          name            VARCHAR(255) NOT NULL,
                          code            VARCHAR(20) NOT NULL UNIQUE,
                          unit            VARCHAR(10) NOT NULL,
                          current_price   DECIMAL(10,2) NOT NULL,
                          created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                          INDEX idx_products_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Спецификации
CREATE TABLE product_specifications (
                                        spec_id         INT AUTO_INCREMENT PRIMARY KEY,
                                        product_id      VARCHAR(20) NOT NULL,
                                        material_id     VARCHAR(20) NOT NULL,
                                        quantity        DECIMAL(10,4) NOT NULL,
                                        unit            VARCHAR(10) NOT NULL,

                                        CONSTRAINT fk_spec_product
                                            FOREIGN KEY (product_id) REFERENCES products(product_id)
                                                ON DELETE CASCADE ON UPDATE CASCADE,

                                        CONSTRAINT fk_spec_material
                                            FOREIGN KEY (material_id) REFERENCES materials(material_id)
                                                ON DELETE RESTRICT ON UPDATE CASCADE,

                                        UNIQUE KEY uk_spec_product_material (product_id, material_id),
                                        INDEX idx_spec_product (product_id),
                                        INDEX idx_spec_material (material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Заказы покупателей
CREATE TABLE orders (
                        order_id        INT AUTO_INCREMENT PRIMARY KEY,
                        order_number    VARCHAR(50) NOT NULL,
                        order_date      DATE NOT NULL,
                        customer_id     VARCHAR(20) NOT NULL,
                        executor_id     VARCHAR(20) NOT NULL,
                        total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,
                        status          VARCHAR(20) DEFAULT 'новый',
                        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                        CONSTRAINT fk_order_customer
                            FOREIGN KEY (customer_id) REFERENCES organizations(org_id)
                                ON DELETE RESTRICT ON UPDATE CASCADE,

                        CONSTRAINT fk_order_executor
                            FOREIGN KEY (executor_id) REFERENCES organizations(org_id)
                                ON DELETE RESTRICT ON UPDATE CASCADE,

                        CHECK (total_amount >= 0),
                        UNIQUE KEY uk_order_number (order_number),
                        INDEX idx_orders_customer (customer_id),
                        INDEX idx_orders_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Позиции заказа
CREATE TABLE order_items (
                             order_item_id   INT AUTO_INCREMENT PRIMARY KEY,
                             order_id        INT NOT NULL,
                             product_id      VARCHAR(20) NOT NULL,
                             quantity        DECIMAL(10,2) NOT NULL,
                             unit_price      DECIMAL(10,2) NOT NULL,
                             amount          DECIMAL(12,2) NOT NULL,

                             CONSTRAINT fk_item_order
                                 FOREIGN KEY (order_id) REFERENCES orders(order_id)
                                     ON DELETE CASCADE ON UPDATE CASCADE,

                             CONSTRAINT fk_item_product
                                 FOREIGN KEY (product_id) REFERENCES products(product_id)
                                     ON DELETE RESTRICT ON UPDATE CASCADE,

                             CHECK (quantity > 0),
                             CHECK (amount >= 0),
                             INDEX idx_order_items_order (order_id),
                             INDEX idx_order_items_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Партии производства
CREATE TABLE production_batches (
                                    batch_id            INT AUTO_INCREMENT PRIMARY KEY,
                                    batch_number        VARCHAR(50) NOT NULL,
                                    production_date     DATE NOT NULL,
                                    product_id          VARCHAR(20) NOT NULL,
                                    planned_quantity    DECIMAL(10,2) NOT NULL,
                                    actual_quantity     DECIMAL(10,2) DEFAULT 0,
                                    status              VARCHAR(20) DEFAULT 'запланирован', -- запланирован, в_производстве, завершен
                                    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                                    CONSTRAINT fk_batch_product
                                        FOREIGN KEY (product_id) REFERENCES products(product_id)
                                            ON DELETE RESTRICT ON UPDATE CASCADE,

                                    CHECK (planned_quantity > 0),
                                    CHECK (actual_quantity >= 0),
                                    UNIQUE KEY uk_batch_number (batch_number),
                                    INDEX idx_production_date (production_date),
                                    INDEX idx_production_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Расход материалов
CREATE TABLE production_materials (
                                      production_material_id  INT AUTO_INCREMENT PRIMARY KEY,
                                      batch_id                INT NOT NULL,
                                      material_id             VARCHAR(20) NOT NULL,
                                      planned_quantity        DECIMAL(10,4) NOT NULL,
                                      actual_quantity         DECIMAL(10,4) NOT NULL,
                                      unit_price              DECIMAL(10,2) NOT NULL,
                                      unit                    VARCHAR(10) NOT NULL,

                                      CONSTRAINT fk_prodmat_batch
                                          FOREIGN KEY (batch_id) REFERENCES production_batches(batch_id)
                                              ON DELETE CASCADE ON UPDATE CASCADE,

                                      CONSTRAINT fk_prodmat_material
                                          FOREIGN KEY (material_id) REFERENCES materials(material_id)
                                              ON DELETE RESTRICT ON UPDATE CASCADE,

                                      UNIQUE KEY uk_prodmat_batch_material (batch_id, material_id),
                                      INDEX idx_prodmat_batch (batch_id),
                                      INDEX idx_prodmat_material (material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10.Запрос Модуль 3
-- Расчет себестоимости
SELECT
    o.order_number AS 'Номер заказа',
    o.order_date AS 'Дата заказа',
    cust.name AS 'Заказчик',
    SUM(oi.quantity) AS 'Общее количество продукции',
    o.total_amount AS 'Сумма продажи',


    ROUND(SUM(
                  oi.quantity *
                  (SELECT SUM(ps.quantity * m.current_price)
                   FROM product_specifications ps
                            JOIN materials m ON ps.material_id = m.material_id
                   WHERE ps.product_id = oi.product_id)
          ), 2) AS 'Себестоимость заказа',


    ROUND(o.total_amount - SUM(
            oi.quantity *
            (SELECT SUM(ps.quantity * m.current_price)
             FROM product_specifications ps
                      JOIN materials m ON ps.material_id = m.material_id
             WHERE ps.product_id = oi.product_id)
                           ), 2) AS 'Маржа',


    ROUND((
              o.total_amount - SUM(
                      oi.quantity *
                      (SELECT SUM(ps.quantity * m.current_price)
                       FROM product_specifications ps
                                JOIN materials m ON ps.material_id = m.material_id
                       WHERE ps.product_id = oi.product_id)
                               )
              ) / o.total_amount * 100, 2) AS 'Рентабельность, %'

FROM orders o
         JOIN organizations cust ON o.customer_id = cust.org_id
         JOIN order_items oi ON o.order_id = oi.order_id

WHERE o.order_id = 1

GROUP BY o.order_id, o.order_number, o.order_date, cust.name, o.total_amount;
-- Детализация
SELECT
    p.name AS 'Продукция',
    oi.quantity AS 'Количество',
    oi.unit_price AS 'Цена продажи',
    oi.amount AS 'Сумма продажи',


    (SELECT ROUND(SUM(ps.quantity * m.current_price), 2)
     FROM product_specifications ps
              JOIN materials m ON ps.material_id = m.material_id
     WHERE ps.product_id = p.product_id) AS 'Себестоимость ед.',


    oi.quantity * (
        SELECT SUM(ps.quantity * m.current_price)
        FROM product_specifications ps
                 JOIN materials m ON ps.material_id = m.material_id
        WHERE ps.product_id = p.product_id
    ) AS 'Себестоимость позиции'

FROM order_items oi
         JOIN products p ON oi.product_id = p.product_id
         JOIN orders o ON oi.order_id = o.order_id

WHERE o.order_id = 1

ORDER BY oi.order_item_id;