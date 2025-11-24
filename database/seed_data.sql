-- ============================================
-- PORTAL DE TRABAJO UTP - SEED DATA
-- ============================================
-- Descripción: Datos iniciales del sistema
-- Incluye: 3 administradores, 10 empresas tech, 30 habilidades, 10 ofertas
-- ============================================

USE portal_trabajo_utp;

-- ============================================
-- ADMINISTRADORES (3 usuarios)
-- ============================================
-- Contraseña para todos: Admin123!
-- NOTA: Los hashes fueron generados con password_hash('Admin123!', PASSWORD_BCRYPT)
-- Si necesitas regenerarlos, usa: php -r "echo password_hash('Admin123!', PASSWORD_BCRYPT);"

INSERT INTO administradores (correo_utp, nombres, apellidos, password_hash) VALUES
('denis.cedeno@utp.ac.pa', 'Denis', 'Cedeño', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('geralis.garrido@utp.ac.pa', 'Geralis', 'Garrido', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('admin.ti@utp.ac.pa', 'Admin', 'TI', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- EMPRESAS (10 empresas tecnológicas de Panamá)
-- ============================================

INSERT INTO empresas (nombre_legal, nombre_comercial, logo, descripcion, sector, sitio_web, email_contacto) VALUES
('Yappy S.A.', 'Yappy', 'yappy.png', 'Solución de pagos móviles líder en Panamá. Transformamos la forma en que los panameños realizan transacciones financieras diariamente.', 'FinTech', 'https://www.yappy.com.pa/', 'rrhh@yappy.com'),

('Banco General S.A.', 'Banco General', 'banco_general.png', 'Institución financiera con más de 60 años de experiencia, líder en innovación digital y banca corporativa en Panamá.', 'Banca', 'https://www.bgeneral.com/', 'talentohumano@bgeneral.com'),

('Liberty Latin America', 'Liberty', 'liberty.png', 'Proveedor líder de servicios de comunicaciones convergentes en América Latina y el Caribe. Operaciones en Cable & Wireless Panamá.', 'Telecomunicaciones', 'https://www.lla.com/', 'careers@cwpanama.com'),

('Dell Technologies', 'Dell', 'dell.png', 'Líder mundial en tecnología empresarial, infraestructura, soluciones cloud y transformación digital con centro de operaciones en Panamá.', 'Tecnología', 'https://www.dell.com/es-pa/lp', 'jobs.panama@dell.com'),

('Copa Airlines', 'Copa Airlines', 'copa.png', 'Aerolínea internacional con el hub más importante de América Latina. Departamento de TI responsable de sistemas de reservas y operaciones.', 'Aviación/Tech', 'https://www.copaair.com/es-pa/', 'rrhh@copaair.com'),

('MultiBank Inc.', 'MultiBank', 'multibank.png', 'Banca moderna con servicios digitales innovadores. Enfocados en ofrecer la mejor experiencia digital a nuestros clientes.', 'Banca', 'https://www.multibank.com.pa/es', 'empleos@multibank.com.pa'),

('BAC Credomatic', 'BAC', 'bac.png', 'Grupo financiero regional con presencia en Centroamérica. Innovación en banca digital y servicios financieros integrales.', 'Banca', 'https://www.baccredomatic.com/', 'recursoshumanos@bac.com'),

('PriceSmart Inc.', 'PriceSmart', 'pricesmart.png', 'Cadena de tiendas de membresía con sistemas tecnológicos avanzados para retail, inventario y logística.', 'Retail/Tech', 'https://www.pricesmart.com', 'empleos@pricesmart.com'),

('Ciudad del Saber', 'Ciudad del Saber', 'ciudad_saber.png', 'Parque tecnológico e innovación. Hub para startups, empresas tech y proyectos de transformación digital en Panamá.', 'Educación/Tech', 'https://www.ciudaddelsaber.org', 'info@ciudaddelsaber.org'),

('Autoridad de Innovación Gubernamental', 'AIG', 'aig.png', 'Entidad responsable de la transformación digital del Estado panameño. Implementación de gobierno electrónico y servicios digitales.', 'Gobierno/Tech', 'https://www.innovacion.gob.pa', 'contacto@innovacion.gob.pa');

-- ============================================
-- HABILIDADES (30 habilidades tecnológicas)
-- ============================================

INSERT INTO habilidades (nombre, categoria) VALUES
-- Lenguajes de programación
('PHP', 'tecnica'),
('JavaScript', 'tecnica'),
('Python', 'tecnica'),
('Java', 'tecnica'),
('C#', 'tecnica'),
('SQL', 'tecnica'),
('TypeScript', 'tecnica'),

-- Frameworks y librerías
('React', 'herramienta'),
('Laravel', 'herramienta'),
('Node.js', 'herramienta'),
('Angular', 'herramienta'),
('Vue.js', 'herramienta'),
('Spring Boot', 'herramienta'),

-- Bases de datos
('MySQL', 'herramienta'),
('PostgreSQL', 'herramienta'),
('MongoDB', 'herramienta'),
('Oracle', 'herramienta'),

-- DevOps y Cloud
('Docker', 'herramienta'),
('AWS', 'herramienta'),
('Azure', 'herramienta'),
('Git', 'herramienta'),
('Kubernetes', 'herramienta'),

-- Metodologías
('Agile/Scrum', 'tecnica'),
('CI/CD', 'tecnica'),

-- Habilidades blandas
('Trabajo en equipo', 'blanda'),
('Liderazgo', 'blanda'),
('Comunicación efectiva', 'blanda'),
('Resolución de problemas', 'blanda'),

-- Idiomas
('Inglés avanzado', 'lenguaje'),
('Inglés intermedio', 'lenguaje');

-- ============================================
-- OFERTAS DE TRABAJO (10 ofertas tecnológicas)
-- ============================================

INSERT INTO ofertas (id_empresa, titulo, descripcion, requisitos, responsabilidades, beneficios, salario_min, salario_max, modalidad, tipo_empleo, nivel_experiencia, ubicacion, fecha_limite) VALUES

(1, 'Desarrollador Full Stack - FinTech', 
'Buscamos desarrollador full stack apasionado por la tecnología financiera para unirse a nuestro equipo de innovación en pagos digitales. Trabajarás en el desarrollo de nuevas funcionalidades para la aplicación móvil Yappy, utilizada por miles de panameños diariamente.',
'- 2+ años de experiencia en desarrollo web\n- Dominio de PHP, JavaScript, MySQL\n- Experiencia comprobable con Laravel y React\n- Conocimientos sólidos en API REST y microservicios\n- Experiencia con Git y metodologías ágiles\n- Pasión por crear código limpio y mantenible',
'- Desarrollo de nuevas funcionalidades para la aplicación Yappy\n- Integración con sistemas de pago y pasarelas bancarias\n- Optimización de rendimiento y escalabilidad\n- Code reviews y mentoría a desarrolladores junior\n- Documentación técnica de soluciones\n- Participación en planificación de sprints',
'- Seguro médico privado para ti y tu familia\n- Horario flexible y posibilidad de home office\n- Capacitaciones constantes en nuevas tecnologías\n- Ambiente innovador y dinámico\n- Snacks y bebidas ilimitadas\n- Día libre de cumpleaños',
1500.00, 2500.00, 'hibrido', 'tiempo_completo', 'mid', 'Ciudad de Panamá - Obarrio', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),

(2, 'Analista de Ciberseguridad',
'Únete al equipo de seguridad informática del Banco General para proteger la infraestructura digital bancaria. Serás responsable de monitorear, detectar y responder a amenazas de seguridad, garantizando la protección de datos de miles de clientes.',
'- Licenciatura en Sistemas, Seguridad Informática o afines\n- Certificaciones: CEH, CISSP, CompTIA Security+ (al menos una)\n- Experiencia en pentesting y análisis de vulnerabilidades\n- Conocimiento profundo en SIEM, firewalls, IDS/IPS\n- Experiencia con herramientas: Nessus, Metasploit, Wireshark\n- Inglés intermedio-avanzado',
'- Monitoreo continuo de amenazas y eventos de seguridad\n- Análisis de vulnerabilidades en aplicaciones y sistemas\n- Implementación de controles de seguridad\n- Respuesta a incidentes de seguridad\n- Elaboración de reportes ejecutivos\n- Evaluaciones de seguridad y pruebas de penetración',
'- Salario competitivo acorde a experiencia\n- Certificaciones pagadas por la empresa\n- Bonos por desempeño trimestrales\n- Seguro de vida y seguro médico premium\n- 30 días de vacaciones\n- Plan de carrera definido',
2000.00, 3200.00, 'presencial', 'tiempo_completo', 'senior', 'Ciudad de Panamá - Marbella', DATE_ADD(CURDATE(), INTERVAL 45 DAY)),

(3, 'Ingeniero de Redes y Telecomunicaciones',
'Liberty busca ingeniero de redes para diseñar, implementar y gestionar infraestructura de telecomunicaciones de última generación. Trabajarás en proyectos de expansión de red y optimización de servicios para clientes corporativos.',
'- Ingeniería en Telecomunicaciones, Redes o Sistemas\n- CCNA certificado (CCNP deseable)\n- 3+ años de experiencia con routers y switches Cisco\n- Conocimientos en fibra óptica, MPLS, BGP\n- Experiencia en proyectos de redes WAN/LAN\n- Disponibilidad para trabajo en campo',
'- Diseño y mantenimiento de arquitecturas de red\n- Configuración de equipos Cisco (routers, switches, firewalls)\n- Soporte técnico nivel 3 a clientes corporativos\n- Optimización de performance de red\n- Elaboración de documentación técnica\n- Gestión de proyectos de expansión',
'- Plan médico familiar completo\n- Capacitación internacional (certificaciones Cisco)\n- Equipos de trabajo de última tecnología\n- Bonos anuales por desempeño\n- Oportunidades de crecimiento regional\n- Vehículo de empresa',
1800.00, 2800.00, 'presencial', 'tiempo_completo', 'mid', 'Panamá Oeste - Chorrera', DATE_ADD(CURDATE(), INTERVAL 25 DAY)),

(4, 'DevOps Engineer - Cloud Solutions',
'Dell Technologies busca ingeniero DevOps para liderar proyectos de infraestructura cloud para clientes empresariales. Trabajarás con las tecnologías más modernas de AWS/Azure implementando soluciones escalables y resilientes.',
'- 3+ años de experiencia en DevOps/SRE\n- Experiencia sólida con AWS o Azure\n- Dominio de Docker, Kubernetes, Terraform\n- Experiencia con CI/CD pipelines (Jenkins, GitLab CI, GitHub Actions)\n- Scripting: Bash, Python\n- Experiencia con monitoreo (Prometheus, Grafana)\n- Inglés avanzado (trabajo con equipos internacionales)',
'- Diseño e implementación de arquitecturas cloud\n- Automatización de deployments y CI/CD\n- Gestión de infraestructura como código (IaC)\n- Implementación de soluciones de monitoreo y logging\n- Optimización de costos en cloud\n- Mentoría técnica al equipo\n- Respuesta a incidentes críticos',
'- Trabajo 100% remoto desde Panamá\n- Laptop de alta gama y equipos necesarios\n- Capacitación en AWS/Azure (certificaciones pagadas)\n- Horario flexible\n- Seguro médico internacional\n- Bono anual de desempeño\n- Stock options de Dell',
2200.00, 3500.00, 'remoto', 'tiempo_completo', 'senior', 'Remoto - Panamá', DATE_ADD(CURDATE(), INTERVAL 60 DAY)),

(5, 'Desarrollador Backend - Sistemas de Reservas',
'Copa Airlines busca backend developer para modernizar sistemas críticos de reservas y operaciones. Trabajarás con tecnología de punta en una de las aerolíneas más importantes de América Latina, impactando a millones de pasajeros.',
'- 2+ años en desarrollo backend con Java o C#\n- Experiencia en arquitectura de microservicios\n- Dominio de API REST y SOAP\n- Experiencia con bases de datos SQL y NoSQL\n- Conocimiento de Spring Boot o .NET Core\n- Metodología Agile/Scrum\n- Inglés intermedio',
'- Desarrollo y mantenimiento de microservicios\n- Integración con sistemas legacy (mainframe)\n- Optimización de queries y performance\n- Desarrollo de APIs para aplicaciones móviles\n- Trabajo en equipo internacional (Miami, Panamá)\n- Participación en on-call rotation',
'- Viajes anuales gratuitos para empleado y familia\n- Seguro médico premium\n- Plan de retiro 401k\n- Gimnasio empresarial\n- Cafetería subsidiada\n- Descuentos en hoteles y alquiler de autos\n- 20 días de vacaciones + feriados',
2000.00, 3000.00, 'hibrido', 'tiempo_completo', 'mid', 'Ciudad de Panamá - Albrook', DATE_ADD(CURDATE(), INTERVAL 40 DAY)),

(6, 'Analista de Datos - Business Intelligence',
'MultiBank requiere analista de datos para crear dashboards ejecutivos, análisis de métricas financieras y reportería estratégica. Serás clave en la toma de decisiones basadas en datos para la alta dirección del banco.',
'- Licenciatura en Sistemas, Estadística, Matemáticas o afines\n- SQL avanzado (queries complejas, optimización)\n- Experiencia con Power BI o Tableau\n- Python para análisis de datos (Pandas, NumPy)\n- Conocimientos en ETL\n- Excel avanzado (macros, tablas dinámicas)\n- Capacidad analítica y atención al detalle',
'- Creación de dashboards interactivos en Power BI\n- Análisis de métricas financieras y KPIs bancarios\n- Desarrollo de procesos ETL\n- Limpieza y transformación de datos\n- Presentaciones ejecutivas con insights\n- Automatización de reportes\n- Colaboración con áreas de negocio',
'- Bonos trimestrales por cumplimiento de objetivos\n- Capacitación continua en herramientas BI\n- Horario flexible (entrada 7-9 am)\n- Seguro médico familiar\n- Préstamos preferenciales\n- 30 días de vacaciones\n- Ambiente de trabajo colaborativo',
1600.00, 2400.00, 'hibrido', 'tiempo_completo', 'junior', 'Ciudad de Panamá - Punta Pacífica', DATE_ADD(CURDATE(), INTERVAL 35 DAY)),

(7, 'QA Automation Engineer',
'BAC Credomatic busca ingeniero de QA para liderar la automatización de pruebas en aplicaciones bancarias críticas. Garantizarás la calidad y estabilidad de sistemas que procesan millones de transacciones diarias.',
'- 2+ años en QA automation\n- Experiencia con Selenium, Cypress o Playwright\n- Lenguajes: JavaScript, Python o Java\n- Experiencia en APIs testing (Postman, REST Assured)\n- Conocimiento de metodología Agile/Scrum\n- CI/CD (Jenkins, GitLab)\n- Experiencia en pruebas de regresión automatizadas',
'- Diseño y desarrollo de test cases automatizados\n- Automatización de pruebas de regresión\n- Pruebas de APIs y servicios backend\n- Integración de tests en pipelines CI/CD\n- Documentación de bugs y defectos\n- Colaboración con desarrollo en mejora continua\n- Mentoría a QA manuales',
'- Certificaciones en testing pagadas (ISTQB)\n- Plan médico completo\n- Día libre de cumpleaños\n- Bono anual según desempeño\n- Capacitación en herramientas de automation\n- Préstamos personales preferenciales\n- 25 días de vacaciones',
1500.00, 2200.00, 'hibrido', 'tiempo_completo', 'mid', 'Ciudad de Panamá - Costa del Este', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),

(8, 'Administrador de Base de Datos (DBA)',
'PriceSmart necesita DBA senior para gestionar infraestructura crítica de bases de datos que soportan operaciones de retail en múltiples países. Responsable de alta disponibilidad, performance y disaster recovery.',
'- Certificación en MySQL, PostgreSQL u Oracle\n- 3+ años como DBA en producción\n- Experiencia en alta disponibilidad y replicación\n- Scripting (Bash, Python) para automatización\n- Conocimientos en backup y disaster recovery\n- Optimización de queries y tuning de performance\n- Disponibilidad para guardias 24/7 (rotación)',
'- Administración de servidores de bases de datos\n- Implementación de backups y estrategias de recuperación\n- Optimización de performance y queries\n- Monitoreo proactivo de bases de datos\n- Troubleshooting de incidentes críticos\n- Gestión de parches y actualizaciones\n- Documentación de procedimientos',
'- Salario competitivo + guardias pagadas\n- Descuentos significativos en tiendas PriceSmart\n- Seguro médico familiar completo\n- Bonos por desempeño anuales\n- Capacitación certificaciones DB\n- 30 días de vacaciones\n- Plan de carrera estructurado',
2000.00, 3000.00, 'presencial', 'tiempo_completo', 'senior', 'Panamá - Condado del Rey', DATE_ADD(CURDATE(), INTERVAL 50 DAY)),

(9, 'Frontend Developer - UI/UX',
'Ciudad del Saber busca frontend developer para crear plataformas educativas innovadoras. Trabajarás en proyectos con impacto social, desarrollando interfaces modernas para startups y organizaciones educativas.',
'- 2+ años en desarrollo frontend\n- Dominio de React, Vue.js o Angular\n- HTML5, CSS3, JavaScript ES6+\n- Experiencia en diseño responsive y mobile-first\n- Conocimientos de accesibilidad web (WCAG)\n- Experiencia con Figma o Adobe XD\n- Git y GitHub',
'- Desarrollo de interfaces educativas interactivas\n- Colaboración estrecha con diseñadores UX\n- Implementación de diseños responsive\n- Optimización de performance web\n- Implementación de animaciones y microinteracciones\n- Code reviews y mejores prácticas\n- Prototipado rápido de ideas',
'- Ambiente académico e innovador\n- Flexibilidad horaria total\n- Capacitación gratuita en cursos de la Ciudad\n- Networking con startups y emprendedores\n- Seguro médico\n- Descuentos en eventos y programas\n- Oportunidad de trabajar en proyectos con impacto social',
1400.00, 2200.00, 'hibrido', 'tiempo_completo', 'junior', 'Clayton - Ciudad del Saber', DATE_ADD(CURDATE(), INTERVAL 45 DAY)),

(10, 'Arquitecto de Soluciones Cloud',
'Autoridad de Innovación Gubernamental busca arquitecto de soluciones para diseñar infraestructura cloud que modernizará servicios del Estado panameño. Impacto directo en millones de ciudadanos mediante transformación digital.',
'- 5+ años en arquitectura de soluciones\n- Certificación AWS Solutions Architect Professional o equivalente\n- Experiencia en proyectos gubernamentales (deseable)\n- Conocimientos en seguridad y compliance (ISO 27001)\n- Experiencia con multi-cloud (AWS, Azure, GCP)\n- Arquitectura de microservicios y serverless\n- Inglés avanzado',
'- Diseño de arquitecturas cloud escalables y seguras\n- Liderar migración de sistemas legacy a cloud\n- Elaboración de documentación técnica y blueprints\n- Asesoría técnica a equipos de desarrollo\n- Definición de estándares y mejores prácticas\n- Evaluación de nuevas tecnologías\n- Presentaciones a stakeholders gubernamentales',
'- Impacto social directo en mejora de servicios públicos\n- Estabilidad laboral en sector público\n- Seguro médico premium familiar\n- Plan de pensiones del Estado\n- 30 días de vacaciones + feriados\n- Capacitación internacional en tecnología\n- Ambiente profesional de alto nivel',
2500.00, 4000.00, 'presencial', 'tiempo_completo', 'senior', 'Panamá - Ancón', DATE_ADD(CURDATE(), INTERVAL 60 DAY));

-- ============================================
-- ASOCIAR HABILIDADES A OFERTAS
-- ============================================

-- Oferta 1: Desarrollador Full Stack - Yappy
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(1, 1),  -- PHP
(1, 2),  -- JavaScript
(1, 6),  -- SQL
(1, 8),  -- React
(1, 9),  -- Laravel
(1, 14), -- MySQL
(1, 21); -- Git

-- Oferta 2: Analista de Ciberseguridad - Banco General
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(2, 3),  -- Python
(2, 21), -- Git
(2, 28), -- Resolución de problemas
(2, 29); -- Inglés avanzado

-- Oferta 3: Ingeniero de Redes - Liberty
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(3, 26), -- Liderazgo
(3, 27), -- Comunicación efectiva
(3, 30); -- Inglés intermedio

-- Oferta 4: DevOps Engineer - Dell
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(4, 3),  -- Python
(4, 18), -- Docker
(4, 19), -- AWS
(4, 21), -- Git
(4, 22), -- Kubernetes
(4, 24), -- CI/CD
(4, 29); -- Inglés avanzado

-- Oferta 5: Desarrollador Backend - Copa Airlines
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(5, 4),  -- Java
(5, 6),  -- SQL
(5, 13), -- Spring Boot
(5, 23), -- Agile/Scrum
(5, 30); -- Inglés intermedio

-- Oferta 6: Analista de Datos - MultiBank
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(6, 3),  -- Python
(6, 6),  -- SQL
(6, 14), -- MySQL
(6, 28); -- Resolución de problemas

-- Oferta 7: QA Automation Engineer - BAC
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(7, 2),  -- JavaScript
(7, 3),  -- Python
(7, 21), -- Git
(7, 23), -- Agile/Scrum
(7, 24); -- CI/CD

-- Oferta 8: Administrador de Base de Datos - PriceSmart
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(8, 3),  -- Python
(8, 6),  -- SQL
(8, 14), -- MySQL
(8, 15), -- PostgreSQL
(8, 17), -- Oracle
(8, 28); -- Resolución de problemas

-- Oferta 9: Frontend Developer - Ciudad del Saber
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(9, 2),  -- JavaScript
(9, 7),  -- TypeScript
(9, 8),  -- React
(9, 12), -- Vue.js
(9, 21), -- Git
(9, 25); -- Trabajo en equipo

-- Oferta 10: Arquitecto de Soluciones Cloud - AIG
INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) VALUES
(10, 18), -- Docker
(10, 19), -- AWS
(10, 20), -- Azure
(10, 22), -- Kubernetes
(10, 26), -- Liderazgo
(10, 29); -- Inglés avanzado

-- ============================================
-- VERIFICACIÓN DE DATOS
-- ============================================

SELECT 'SEED DATA INSERTADO EXITOSAMENTE' AS mensaje;

SELECT 
    (SELECT COUNT(*) FROM administradores) AS total_admins,
    (SELECT COUNT(*) FROM empresas) AS total_empresas,
    (SELECT COUNT(*) FROM habilidades) AS total_habilidades,
    (SELECT COUNT(*) FROM ofertas) AS total_ofertas,
    (SELECT COUNT(*) FROM ofertas_habilidades) AS total_ofertas_habilidades;