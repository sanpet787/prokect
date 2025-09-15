<?php
/*
|--------------------------------------------------------------------------
| ส่วนหัวไฟล์ (Thai Doc Block)
|--------------------------------------------------------------------------
| ไฟล์นี้รับผิดชอบ: แบบประเมินความเสี่ยงไซเบอร์และ Incident Response Plan
| Flow: รับ input จากผู้ใช้, ประเมินความเสี่ยง, แสดงผล, ส่งออกไฟล์
| Input: responses จากแบบฟอร์ม
| Output: UI, export PDF/CSV/JSON
| Error: ตรวจสอบ null, handle export error, validate input
| ส่วนประกอบ: ฟอร์ม, ผลลัพธ์, IRP, export, modal
*/
?>
<?php
// ---------------------------
// ส่วน PHP: กำหนดชุดคำถามสำหรับประเมินความเสี่ยง
// ---------------------------
$questions = [
  [
    "section" => "การจัดการข้อมูล", // เพิ่มหมวด
    "icon" => "🗂️",
    "text" => "มีรายการระบบ/ข้อมูลสำคัญ ระบุชัดเจนหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "หากไม่มีการระบุข้อมูลสำคัญ อาจทำให้ขาดการควบคุมและเสี่ยงต่อการรั่วไหลหรือสูญหายของข้อมูลสำคัญ",
    "fix" => "จัดทำรายการข้อมูลและระบบสำคัญให้ชัดเจน",
    "steps" => [
      "สำรวจและระบุข้อมูล/ระบบที่สำคัญขององค์กร",
      "จัดทำเอกสารหรือ inventory",
      "ทบทวนรายการเป็นประจำ"
    ]
  ],
  [
    "section" => "การจัดการข้อมูล",
    "icon" => "🗂️",
    "text" => "ข้อมูลสำคัญถูกจัดระดับความลับหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลสำคัญอาจถูกเข้าถึงหรือเปิดเผยโดยไม่ได้รับอนุญาต",
    "fix" => "กำหนดระดับความลับของข้อมูลแต่ละประเภท",
    "steps" => [
      "กำหนด policy การจัดระดับข้อมูล",
      "ติดป้ายหรือกำหนด label ให้ข้อมูล",
      "อบรมพนักงานเรื่องการจัดการข้อมูล"
    ]
  ],
  [
    "section" => "การควบคุมสิทธิ์",
    "icon" => "🔑",
    "text" => "การเข้าถึงไฟล์/โฟลเดอร์สำคัญจำกัดเฉพาะผู้ที่จำเป็นหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลสำคัญอาจถูกเข้าถึงโดยผู้ไม่มีสิทธิ์ เสี่ยงต่อการรั่วไหลหรือแก้ไขข้อมูล",
    "fix" => "จำกัดสิทธิ์การเข้าถึงเฉพาะผู้ที่จำเป็น",
    "steps" => [
      "ตรวจสอบสิทธิ์การเข้าถึงไฟล์/โฟลเดอร์",
      "ปรับสิทธิ์ให้เหมาะสม",
      "ทบทวนสิทธิ์เป็นประจำ"
    ]
  ],
  [
    "section" => "การควบคุมสิทธิ์",
    "icon" => "🔑",
    "text" => "เปิดใช้ 2FA/MFA กับบัญชีสำคัญหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "บัญชีอาจถูกแฮกได้ง่ายขึ้นหากไม่มี 2FA/MFA",
    "fix" => "เปิดใช้งาน 2FA/MFA สำหรับบัญชีสำคัญ",
    "steps" => [
      "เลือกวิธี 2FA/MFA ที่เหมาะสม",
      "ตั้งค่าในระบบ/บัญชี",
      "แจ้งและอบรมผู้ใช้"
    ]
  ],
  [
    "text" => "ใช้หลักการสิทธิ์เท่าที่จำเป็น (Least Privilege) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ผู้ใช้หรือระบบอาจมีสิทธิ์เกินความจำเป็น เสี่ยงต่อการถูกโจมตีหรือใช้งานผิดวัตถุประสงค์",
    "fix" => "กำหนดสิทธิ์เฉพาะที่จำเป็นต่อหน้าที่",
    "steps" => [
      "ทบทวนสิทธิ์ของผู้ใช้และระบบ",
      "ปรับลดสิทธิ์ที่ไม่จำเป็น",
      "ตรวจสอบเป็นระยะ"
    ]
  ],
  [
    "text" => "แยกบัญชี Admin ออกจากบัญชีใช้งานปกติ และทบทวนสิทธิ์เป็นระยะหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "บัญชี admin ถูกใช้ในงานประจำวัน เสี่ยงต่อการถูกโจมตีและขยายผล",
    "fix" => "แยกบัญชี admin และบัญชีใช้งานทั่วไป",
    "steps" => [
      "สร้างบัญชี admin แยกจาก user ปกติ",
      "ใช้บัญชี admin เฉพาะเวลาจำเป็น",
      "ทบทวนสิทธิ์ admin เป็นประจำ"
    ]
  ],
  [
    "text" => "มีขั้นตอนรับเข้า/ย้ายงาน/ลาออก ที่ปรับ/ปิดสิทธิ์ทันทีหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "อดีตพนักงานหรือผู้ย้ายงานอาจยังเข้าถึงระบบได้",
    "fix" => "กำหนดขั้นตอนปิดสิทธิ์เมื่อมีการเปลี่ยนแปลงสถานะ",
    "steps" => [
      "แจ้ง HR/IT เมื่อมีการเปลี่ยนแปลง",
      "ปิด/ปรับสิทธิ์ทันที",
      "ตรวจสอบสิทธิ์หลังการเปลี่ยนแปลง"
    ]
  ],
  [
    "section" => "การอัปเดต/ช่องโหว่",
    "icon" => "🛡️",
    "text" => "อัปเดตแพตช์ความปลอดภัยตรงเวลา (เช่น ช่องโหว่สำคัญภายใน 14 วัน) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ระบบอาจถูกโจมตีผ่านช่องโหว่ที่ยังไม่ได้อัปเดต",
    "fix" => "อัปเดตแพตช์ความปลอดภัยตามรอบเวลา",
    "steps" => [
      "ติดตามประกาศช่องโหว่",
      "วางแผนและดำเนินการอัปเดต",
      "ทดสอบระบบหลังอัปเดต"
    ]
  ],
  [
    "text" => "มีการสแกนช่องโหว่ระบบสำคัญเป็นประจำหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ช่องโหว่ที่ยังไม่ถูกค้นพบอาจถูกใช้โจมตี",
    "fix" => "สแกนช่องโหว่ระบบสำคัญอย่างสม่ำเสมอ",
    "steps" => [
      "เลือกเครื่องมือสแกนช่องโหว่",
      "กำหนดรอบการสแกน",
      "แก้ไขช่องโหว่ที่พบ"
    ]
  ],
  [
    "section" => "การสำรองข้อมูล",
    "icon" => "💾",
    "text" => "มี Asset Inventory ที่อัปเดตเสมอหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ไม่ทราบว่ามีอุปกรณ์หรือระบบใดบ้าง เสี่ยงต่อการควบคุมและป้องกัน",
    "fix" => "จัดทำและอัปเดต Asset Inventory",
    "steps" => [
      "รวบรวมข้อมูลอุปกรณ์/ระบบทั้งหมด",
      "บันทึกในระบบหรือเอกสาร",
      "อัปเดตเมื่อมีการเปลี่ยนแปลง"
    ]
  ],
  [
    "text" => "สำรองข้อมูลของระบบ/ไฟล์สำคัญตามรอบเวลาหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลอาจสูญหายถ้าไม่มีการสำรองข้อมูล",
    "fix" => "สำรองข้อมูลตามรอบเวลาที่กำหนด",
    "steps" => [
      "กำหนดรอบเวลาสำรองข้อมูล",
      "เลือกวิธีสำรองที่เหมาะสม",
      "ทดสอบการกู้คืนข้อมูล"
    ]
  ],
  [
    "text" => "มีสำรองแบบ Offline/Immutable/Offsite เพื่อกัน Ransomware หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลสำรองอาจถูกโจมตีพร้อมกับข้อมูลหลัก",
    "fix" => "ใช้การสำรองแบบ Offline/Immutable/Offsite",
    "steps" => [
      "กำหนดนโยบายสำรองข้อมูล",
      "เลือกวิธีสำรองที่ปลอดภัย",
      "ทดสอบการกู้คืนจากสำรอง"
    ]
  ],
  [
    "section" => "การตรวจจับเหตุการณ์",
    "icon" => "🔎",
    "text" => "กำหนด RTO/RPO แบบเข้าใจง่ายไว้หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ไม่ทราบระยะเวลาที่ต้องการกู้คืนระบบ/ข้อมูล เสี่ยงต่อการฟื้นฟูไม่ทันเวลา",
    "fix" => "กำหนด RTO/RPO ให้ชัดเจน",
    "steps" => [
      "วิเคราะห์ความต้องการธุรกิจ",
      "กำหนด RTO/RPO",
      "แจ้งให้ทีมงานรับทราบ"
    ]
  ],
  [
    "text" => "เคยทดสอบกู้คืนจริงใน 6–12 เดือนที่ผ่านมาและบันทึกผลหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "อาจกู้คืนข้อมูลไม่ได้จริงเมื่อเกิดเหตุ",
    "fix" => "ทดสอบการกู้คืนข้อมูลเป็นประจำ",
    "steps" => [
      "วางแผนทดสอบการกู้คืน",
      "ดำเนินการทดสอบ",
      "บันทึกผลและปรับปรุง"
    ]
  ],
  [
    "section" => "การตอบสนองเหตุการณ์",
    "icon" => "🚨",
    "text" => "รวบรวม Logs ไว้ที่จุดกลาง (เช่น Syslog/SIEM) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูล Log อาจกระจัดกระจาย ตรวจสอบย้อนหลังได้ยาก",
    "fix" => "รวบรวม Log ไว้ที่จุดกลาง",
    "steps" => [
      "เลือกโซลูชัน Log Centralization",
      "ตั้งค่าระบบให้ส่ง Log",
      "ตรวจสอบ Log เป็นประจำ"
    ]
  ],
  [
    "text" => "มีระบบแจ้งเตือนเหตุผิดปกติและมีคนเฝ้าดูในเวลางานหรือ 24/7 หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เหตุผิดปกติอาจถูกตรวจพบล่าช้า",
    "fix" => "ตั้งค่าระบบแจ้งเตือนและมีผู้รับผิดชอบ",
    "steps" => [
      "กำหนดเหตุการณ์ที่ต้องแจ้งเตือน",
      "ตั้งค่าระบบแจ้งเตือน",
      "มอบหมายผู้รับผิดชอบ"
    ]
  ],
  [
    "section" => "การป้องกันมัลแวร์",
    "icon" => "🦠",
    "text" => "ทุกเครื่องมี Antivirus/EDR และอัปเดตอัตโนมัติหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เครื่องอาจติดมัลแวร์หากไม่มีการป้องกันหรืออัปเดต",
    "fix" => "ติดตั้ง Antivirus/EDR และตั้งค่าอัปเดตอัตโนมัติ",
    "steps" => [
      "เลือกโซลูชัน Antivirus/EDR",
      "ติดตั้งในทุกเครื่อง",
      "ตั้งค่าให้อัปเดตอัตโนมัติ"
    ]
  ],
  [
    "section" => "การบริหารจัดการ",
    "icon" => "👔",
    "text" => "มีแผน IRP เป็นลายลักษณ์อักษรและอัปเดตไม่เกิน 12 เดือนหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ขาดแผนรับมือเหตุการณ์ อาจตอบสนองล่าช้าเมื่อเกิดเหตุ",
    "fix" => "จัดทำและอัปเดตแผน IRP",
    "steps" => [
      "เขียนแผน IRP ให้ครบถ้วน",
      "ทบทวนและอัปเดตทุกปี",
      "แจ้งให้ทีมงานรับทราบ"
    ]
  ],
  [
    "text" => "มีรายชื่อผู้รับผิดชอบ/เบอร์ติดต่อฉุกเฉิน (ใน–นอกองค์กร) ที่เข้าถึงได้เร็วหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ติดต่อผู้เกี่ยวข้องไม่ได้ทันทีเมื่อเกิดเหตุ",
    "fix" => "จัดทำรายชื่อและช่องทางติดต่อฉุกเฉิน",
    "steps" => [
      "รวบรวมรายชื่อและเบอร์ติดต่อ",
      "จัดเก็บในที่เข้าถึงง่าย",
      "ทบทวนและอัปเดตข้อมูล"
    ]
  ],
  [
    "text" => "มี Playbook อย่างน้อยฟิชชิง/มัลแวร์/แรนซัมแวร์หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ขาดแนวทางปฏิบัติเมื่อเกิดเหตุเฉพาะหน้า",
    "fix" => "จัดทำ Playbook สำหรับเหตุการณ์สำคัญ",
    "steps" => [
      "ระบุเหตุการณ์ที่พบบ่อย",
      "เขียนขั้นตอนรับมือแต่ละเหตุการณ์",
      "อบรมทีมงาน"
    ]
  ],
  [
    "text" => "มีขั้นตอนควบคุมเหตุไม่ให้ลาม และเคยซ้อมแล้วหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เหตุการณ์อาจลุกลามขยายวงกว้าง",
    "fix" => "กำหนดขั้นตอนควบคุมเหตุและซ้อมรับมือ",
    "steps" => [
      "วางแผนขั้นตอนควบคุมเหตุ",
      "ซ้อมรับมือกับทีมงาน",
      "ปรับปรุงขั้นตอนตามผลซ้อม"
    ]
  ],
  [
    "text" => "เคยซ้อมสถานการณ์ปีละครั้ง และสรุปบทเรียน/ปรับแผนหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ทีมงานอาจไม่พร้อมรับมือเหตุการณ์จริง",
    "fix" => "ซ้อมสถานการณ์และสรุปบทเรียนทุกปี",
    "steps" => [
      "วางแผนซ้อมสถานการณ์",
      "ดำเนินการซ้อม",
      "สรุปบทเรียนและปรับแผน"
    ]
  ],
  [
    "section" => "การปฏิบัติตามกฎหมาย",
    "icon" => "📜",
    "text" => "แผน BCP/DR สอดคล้องกับ IRP และเคยทดสอบหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "แผน BCP/DR ไม่สอดคล้องกับ IRP อาจฟื้นฟูระบบไม่ได้ตามเป้าหมาย",
    "fix" => "ทบทวนและทดสอบแผน BCP/DR ให้สอดคล้องกับ IRP",
    "steps" => [
      "เปรียบเทียบแผน BCP/DR กับ IRP",
      "ปรับปรุงให้สอดคล้องกัน",
      "ทดสอบแผนเป็นประจำ"
    ]
  ],
  [
    "text" => "ทราบ PDPA/ข้อกำหนดลูกค้า และขั้นตอนการแจ้งเหตุหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "อาจละเมิดกฎหมายหรือข้อกำหนดลูกค้าเมื่อเกิดเหตุ",
    "fix" => "ศึกษาข้อกำหนดและกำหนดขั้นตอนแจ้งเหตุ",
    "steps" => [
      "ศึกษากฎหมาย/ข้อกำหนดที่เกี่ยวข้อง",
      "กำหนดขั้นตอนแจ้งเหตุ",
      "อบรมทีมงาน"
    ]
  ],
  [
    "text" => "มีช่องทางติดต่อ CERT/ผู้เชี่ยวชาญภายนอกไว้ล่วงหน้าหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ไม่สามารถขอความช่วยเหลือจากผู้เชี่ยวชาญได้ทันที",
    "fix" => "เตรียมช่องทางติดต่อ CERT/ผู้เชี่ยวชาญไว้ล่วงหน้า",
    "steps" => [
      "ค้นหาข้อมูล CERT/ผู้เชี่ยวชาญ",
      "บันทึกช่องทางติดต่อ",
      "ทดสอบการติดต่อ"
    ]
  ],
  [
    "section" => "โครงสร้างพื้นฐาน",
    "icon" => "🌐",
    "text" => "เครือข่ายแบ่งโซน/เซกเมนต์ลดการลามของเหตุหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เหตุการณ์อาจลุกลามไปยังระบบอื่นได้ง่าย",
    "fix" => "แบ่งโซนเครือข่ายเพื่อลดการลาม",
    "steps" => [
      "วิเคราะห์โครงสร้างเครือข่าย",
      "กำหนดโซน/segment",
      "ตั้งค่า firewall ระหว่างโซน"
    ]
  ],
  [
    "text" => "ทบทวนกฎไฟร์วอลล์ ปิดพอร์ต/บริการที่ไม่จำเป็นเป็นระยะหรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "เปิดพอร์ต/บริการที่ไม่จำเป็น เสี่ยงต่อการถูกโจมตี",
    "fix" => "ทบทวนและปิดพอร์ต/บริการที่ไม่จำเป็น",
    "steps" => [
      "ตรวจสอบกฎ firewall",
      "ปิดพอร์ต/บริการที่ไม่ใช้",
      "ทบทวนเป็นระยะ"
    ]
  ],
  [
    "section" => "Cloud/คลาวด์",
    "icon" => "☁️",
    "text" => "คลาวด์ตั้งค่าปลอดภัยพื้นฐาน (MFA/Logs/Encryption) หรือไม่?",
    "type" => "radio",
    "choices" => [
      ["label" => "ใช่", "score" => 0],
      ["label" => "ไม่แน่ใจ", "score" => 1],
      ["label" => "ไม่ใช่", "score" => 2]
    ],
    "risk" => "ข้อมูลหรือระบบบนคลาวด์อาจถูกเข้าถึงหรือโจมตีได้ง่าย",
    "fix" => "ตั้งค่าความปลอดภัยพื้นฐานบนคลาวด์",
    "steps" => [
      "เปิดใช้ MFA",
      "เปิด Logging",
      "ตั้งค่า Encryption"
    ]
  ]
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Cyber IRP Risk Assessment (PHP)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/avif" href="logo.avif">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;700&family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
  <style>
    /* ---------------------------
       ส่วน CSS: ตกแต่งหน้าตาแบบฟอร์ม
       --------------------------- */
    body {
      background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);
    }
    .cyber-glow {
      /* ใส่เงาให้หัวข้อ */
      text-shadow: 0 2px 8px #38bdf8, 0 0px 2px #0ea5e9;
      letter-spacing: 0.03em;
    }
    .question-card {
      /* กล่องคำถาม */
      background: #fff;
      border: none;
      border-radius: 1.2rem;
      box-shadow: 0 4px 24px 0 #38bdf822;
      max-width: 600px;
      margin: 0 auto;
      transition: box-shadow 0.2s;
      padding: 2.5rem 2rem 2rem 2rem;
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
      animation: fadeInUp 0.5s;
    }
    @keyframes fadeInUp {
      from { opacity:0; transform: translateY(30px);}
      to { opacity:1; transform: translateY(0);}
    }
    .question-card:after {
      /* เส้น gradient ใต้กล่อง */
      content: "";
      position: absolute;
      left: 1.5rem; right: 1.5rem; bottom: 0;
      height: 4px;
      border-radius: 2px;
      background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
      opacity: 0.12;
    }
    .question-progress {
      /* แถบ progress */
      background: #e0f2fe;
      border-radius: 1rem;
      overflow: hidden;
      height: 12px;
      margin-bottom: 2rem;
      box-shadow: 0 1px 4px #38bdf822;
    }
    .question-progress-bar {
      /* progress bar สีฟ้า */
      background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
      height: 100%;
      transition: width 0.4s cubic-bezier(.4,2,.6,1);
    }
    .radio-group {
      /* กลุ่ม radio button */
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 0.5rem;
    }
    .radio-group label {
      /* ปุ่มตัวเลือก */
      background: #f1f5f9;
      border-radius: 0.5rem;
      padding: 0.45rem 1.2rem 0.45rem 1rem;
      font-weight: 500;
      cursor: pointer;
      border: 1.5px solid transparent;
      transition: border 0.2s, background 0.2s;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 1rem;
    }
    .radio-group input[type="radio"] {
      margin-right: 0.3rem;
    }
    .radio-group input[type="radio"]:checked + span {
      color: #0ea5e9;
      font-weight: 700;
    }
    .radio-group input[type="radio"]:focus + span {
      outline: 2px solid #38bdf8;
    }
    .question-section {
      /* หมวดหมู่ (ถ้ามี) */
      color: #0ea5e9;
      font-weight: 600;
      font-size: 1.05rem;
      margin-bottom: 0.2rem;
      letter-spacing: 0.01em;
    }
    .question-number {
      /* หมายเลขข้อ */
      position: absolute;
      top: 1.2rem;
      right: 2rem;
      color: #b6c7d6;
      font-size: 1.1rem;
      font-weight: 600;
      letter-spacing: 0.04em;
    }
    .question-card input[type="text"] {
      /* ช่องหมายเหตุ */
      background: #f8fafc;
      border: 1.5px solid #e0f2fe;
      border-radius: 0.6rem;
      padding: 0.7rem 1rem;
      font-size: 1rem;
      margin-top: 0.2rem;
      transition: border 0.2s;
      width: 100%;
    }
    .question-card input[type="text"]:focus {
      border-color: #38bdf8;
      outline: none;
      background: #fff;
    }
    .btn-main {
      /* ปุ่มถัดไป */
      background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
      color: #fff;
      font-weight: 600;
      border-radius: 0.7rem;
      padding: 0.7rem 2.2rem;
      box-shadow: 0 2px 8px #38bdf822;
      transition: background 0.2s, box-shadow 0.2s;
      border: none;
    }
    .btn-main:hover, .btn-export:hover {
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 6px 24px #38bdf822;
    }
    .btn-secondary {
      /* ปุ่มย้อนกลับ */
      background: #f1f5f9;
      color: #0ea5e9;
      font-weight: 600;
      border-radius: 0.7rem;
      padding: 0.7rem 2.2rem;
      border: none;
      transition: background 0.2s;
    }
    .btn-secondary:hover {
      background: #e0f2fe;
    }
    .btn-group > * { margin: 0 0.3rem 0.5rem 0.3rem; }
    @media (max-width: 600px) {
      /* Responsive สำหรับมือถือ */
      .question-card { padding: 1.2rem 0.7rem 1.2rem 0.7rem; }
      .question-number { right: 1rem; top: 0.7rem; }
      .radio-group label { font-size: 0.97rem; padding: 0.4rem 0.8rem 0.4rem 0.7rem; }
    }
    @media print {
      /* สำหรับพิมพ์/Export PDF */
      .print\:hidden { display: none !important; }
      body { background: #fff !important; }
      .question-card { box-shadow: none !important; border: 1px solid #bbb !important; }
      #exportBtns { display: none !important; }
    }
    .panel-header { font-size:1.25rem; font-weight:700; color:#2563eb; background:#f1f5f9; border-radius:0.7rem 0.7rem 0 0; padding:1rem 1.5rem; cursor:pointer; display:flex; align-items:center; justify-content:space-between; }
    .panel-content { display:none; background:#fff; border-radius:0 0 0.7rem 0.7rem; border:1px solid #e0e7ef; border-top:none; padding:1.5rem; }
    .panel.active .panel-content { display:block; }
    .panel { margin-bottom:2rem; box-shadow:0 2px 12px #38bdf822; border-radius:0.7rem; }
    .panel-header svg { transition:transform 0.2s; }
    .panel.active .panel-header svg { transform:rotate(90deg);}
    .result-table { width:100%; border-collapse:collapse; margin-bottom:1.5rem; }
    .result-table th, .result-table td { border:1px solid #e0e7ef; padding:0.7em 1em; text-align:left; }
    .result-table th { background:#f1f5f9; font-weight:700; }
    .result-table tr:nth-child(even) { background:#f8fafc; }
    .risk-high { color:#dc2626; font-weight:bold; }
    .risk-medium { color:#d97706; font-weight:bold; }
    .risk-low { color:#16a34a; font-weight:bold; }
    .heatmap-table { border-collapse:collapse; margin:1.5rem auto; }
    .heatmap-table td, .heatmap-table th { width:60px; height:40px; text-align:center; font-weight:600; border:1px solid #e0e7ef; }
    .heatmap-low { background:#bbf7d0; }
    .heatmap-medium { background:#fde68a; }
    .heatmap-high { background:#fecaca; }
    .stepper { border-left:4px solid #38bdf8; margin-left:1.5rem; padding-left:2rem; }
    .stepper-phase { position:relative; margin-bottom:2.5rem; }
    .stepper-phase:last-child { margin-bottom:0; }
    .stepper-dot { position:absolute; left:-2.2rem; top:0.2rem; width:1.3rem; height:1.3rem; background:#38bdf8; border-radius:50%; border:3px solid #fff; box-shadow:0 0 0 2px #38bdf8; }
    .stepper-title { font-size:1.1rem; font-weight:700; color:#0ea5e9; margin-bottom:0.5rem; }
    .stepper-table { margin-bottom:0.7rem; }
    .stepper-table th, .stepper-table td { font-size:0.98rem; }
    .summary-card { background:#f1f5f9; border-radius:0.7rem; padding:1.2rem 1.5rem; margin-bottom:1.5rem; font-size:1.1rem; }
    .export-btns { margin-top:2rem; display:flex; flex-wrap:wrap; gap:0.7rem; justify-content:center; }
    .btn-export { background:#f1f5f9; color:#0ea5e9; font-weight:600; border-radius:0.7rem; padding:0.7rem 2.2rem; border:none; transition:background 0.2s; }
    .btn-export:hover { background:#e0f2fe; }
    /* ---------------------------
       ส่วน CSS: ปรับแต่งสำหรับผลลัพธ์ Pro Report
       --------------------------- */
    .panel-header { background: #f1f5f9; }
    .panel-header span { font-weight: 600; }
    .panel-content { background: #fff; border: 1px solid #e0e7ef; }
    .result-table th {
      background: #f1f5f9;
      color: #0e2233;
      font-weight: 600;
    }
    .result-table td {
      background: #fff;
      color: #0e2233;
    }
    .risk-high { color:#dc2626; font-weight:bold; }
    .risk-medium { color:#d97706; font-weight:bold; }
    .risk-low { color:#16a34a; font-weight:bold; }
    .heatmap-table td, .heatmap-table th {
      background: #f1f5f9;
      color: #0e2233;
      font-weight: 600;
    }
    .stepper-dot { background:#38bdf8; }
    .stepper-title { color:#0ea5e9; }
    /* ---------------------------
       ส่วน CSS: ปรับแต่งสำหรับ Modal
       --------------------------- */
    .modal-content {
      border-radius: 1.2rem;
      box-shadow: 0 4px 24px 0 #38bdf822;
    }
    .modal-header {
      border-bottom: 1px solid #e0e7ef;
    }
    .modal-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #0e2233;
    }
    .modal-body {
      font-size: 1rem;
      color: #0e2233;
    }
    /* ปรับขนาด modal สำหรับหน้าจอเล็ก */
    @media (max-width: 768px) {
      .modal-dialog {
        max-width: 90%;
        margin: 1rem auto;
      }
    }
  </style>
  <!-- เพิ่ม Bootstrap 5 CSS/JS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="max-w-xl mx-auto my-8 p-2">
  <!-- ---------------------------
       ส่วนหัวแบบฟอร์ม (โลโก้/หัวข้อ)
       --------------------------- -->
  <div class="flex flex-col items-center mb-8">
    <!-- โลโก้บริษัทแบบโปร (ใช้ไฟล์ logo.avif) -->
    <img 
      src="logo.avif" 
      alt="HackerProtect Pro Logo" 
      style="
        width:110px;
        margin-bottom:18px;
        border-radius: 22px;
        box-shadow: 0 6px 32px 0 #38bdf855, 0 1px 2px #0ea5e955;
        background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);
        border: 2.5px solid #38bdf8;
        padding: 8px;
        transition: box-shadow 0.2s;
      "
      class="hover:scale-105 transition-transform duration-200"
    >
    <!-- หัวข้อหลักแบบ Paradise Resort Style -->
    <h1 
      class="text-4xl md:text-5xl font-extrabold text-center mb-2"
      style="
        color: #0e2233;
        letter-spacing: 0.03em;
        text-shadow:
          0 6px 32px #38bdf822,
          0 2px 8px #0ea5e9,
          0 1px 0 #fff,
          0 0 2px #fff;
        font-family: 'Prompt', 'Sarabun', 'Segoe UI', Arial, sans-serif;
        background: none;
        -webkit-background-clip: initial;
        -webkit-text-fill-color: initial;
        background-clip: initial;
      "
    >
      CHECK RISK
      <span 
        class="align-super font-bold"
        style="
          font-size:0.7em;
          margin-left:0.2em;
          letter-spacing:0.05em;
          color: #38bdf8;
          text-shadow:0 2px 8px #38bdf8, 0 0px 2px #0ea5e9;
          font-family: inherit;
        "
      >
    </div>
    <p 
      class="text-center text-cyan-800 text-lg md:text-xl font-medium mb-2"
      style="font-family: 'Prompt', 'Sarabun', 'Segoe UI', Arial, sans-serif;"
    >
      แบบประเมินความพร้อมและความปลอดภัยไซเบอร์ภายในองค์กร
    </p>
  </div>
  <!-- ---------------------------
       ส่วนฟอร์มคำถาม
       --------------------------- -->
  <section id="formSection">
    <!-- Progress bar -->
    <div class="question-progress">
      <div id="progressBar" class="question-progress-bar" style="width:0%"></div>
    </div>
    <form id="assessmentForm" autocomplete="off">
      <div id="questionBox"></div>
      <!-- ปุ่มย้อนกลับ/ถัดไป -->
      <div class="btn-group flex flex-row gap-2 mt-4 justify-center print:hidden">
        <button type="button" id="prevBtn" class="btn-secondary" style="min-width:110px;">ย้อนกลับ</button>
        <button type="button" id="nextBtn" class="btn-main" style="min-width:110px;">ถัดไป</button>
      </div>
    </form>
  </section>
  <!-- =========================
       ผลลัพธ์ Risk Assessment & IRP
       ========================= -->
  <section id="proResultsSection" style="display:none;">
    <h2 class="text-2xl font-bold mb-6 text-center text-blue-800">Risk Assessment Results</h2>
    <!-- Collapsible Panels -->
    <div class="panel active" id="panel-risk">
      <div class="panel-header">
        <span>Cybersecurity Risk Assessment</span>
        <svg width="18" height="18" fill="none"><path d="M6 7l3 3 3-3" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round"/></svg>
      </div>
      <div class="panel-content">
        <div class="mb-6">
          <div class="font-bold text-lg mb-2">Risk Scenario Table</div>
          <table class="result-table">
            <thead>
              <tr>
                <th>Asset</th>
                <th>Threat Event</th>
                <th>Vulnerability</th>
                <th>Consequence</th>
              </tr>
            </thead>
            <tbody id="riskScenarioRows"></tbody>
          </table>
        </div>
        <div class="mb-6">
          <div class="font-bold text-lg mb-2">Likelihood & Impact Matrix</div>
          <table class="result-table">
            <thead>
              <tr>
                <th>Asset</th>
                <th>Likelihood (1-3)</th>
                <th>Impact (1-3)</th>
                <th>Risk Level</th>
              </tr>
            </thead>
            <tbody id="riskMatrixRows"></tbody>
          </table>
          <div class="mt-4">
            <div class="font-bold text-lg mb-2">Risk Heatmap</div>
            <table class="heatmap-table">
              <tr>
                <th rowspan="2" style="writing-mode:vertical-lr;transform:rotate(180deg);">Impact</th>
                <th colspan="3">Likelihood</th>
              </tr>
              <tr>
                <th>1</th><th>2</th><th>3</th>
              </tr>
              <tbody id="heatmapGrid"></tbody>
            </table>
          </div>
        </div>
        <div>
          <div class="font-bold text-lg mb-2">Risk Response Table</div>
          <table class="result-table">
            <thead>
              <tr>
                <th>Asset</th>
                <th>Risk Level</th>
                <th>Recommended Response</th>
              </tr>
            </thead>
            <tbody id="riskResponseRows"></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="panel" id="panel-irp">
      <div
        id="irp-toggle"
        class="panel-header"
        tabindex="0"
        role="button"
        aria-expanded="false"
        aria-controls="irp-panel"
        data-testid="irp-toggle"
        style="user-select:none;outline:none;display:flex;align-items:center;justify-content:space-between;cursor:pointer;"
      >
        <span>Incident Response Plan</span>
        <svg id="irp-caret" width="18" height="18" fill="none" style="transition:transform 0.3s;">
          <path d="M6 7l3 3 3-3" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <div
        id="irp-panel"
        class="panel-content"
        data-testid="irp-panel"
        style="overflow:hidden;max-height:0;transition:max-height 0.4s cubic-bezier(.4,2,.6,1);"
        aria-hidden="true"
      >
        <!-- ...เนื้อหา IRP เดิม... -->
        <div class="summary-card mb-6" id="incidentSummary"></div>
        <div class="mb-6">
          <div class="font-bold text-lg mb-2">CIRT Team Roles</div>
          <table class="result-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Responsibility</th>
              </tr>
            </thead>
            <tbody id="cirtRolesRows"></tbody>
          </table>
        </div>
        <div>
          <div class="font-bold text-lg mb-2">Incident Response Phases</div>
          <div class="stepper" id="irpStepper"></div>
        </div>
      </div>
    </div>
    <div class="export-btns print:hidden">
      <button type="button" class="btn-export" id="exportPDF2">Export PDF</button>
      <button type="button" class="btn-export" id="exportCSV2">Export CSV</button>
      <button type="button" class="btn-export" id="exportJSON2">Export JSON</button>
    </div>
  </section>
  <!-- ---------------------------
       ส่วนสรุปผลและปุ่ม Export
       --------------------------- -->
  <section id="summarySection" style="display:none">
    <div id="summary" class="mt-10"></div>
    <div id="exportBtns" class="export-btns print:hidden">
      <button type="button" class="btn-export" id="exportPDF">Export PDF</button>
      <button type="button" class="btn-export" id="exportCSV">Export CSV</button>
      <button type="button" class="btn-export" id="exportJSON">Export JSON</button>
      <button type="button" class="btn-export" onclick="window.print()">Print</button>
    </div>
  </section>
  <!-- ---------------------------
       ส่วน Footer
       --------------------------- -->
  <footer class="mt-12 text-center text-xs text-cyan-700 print:text-black">
    &copy; <?= date('Y') ?> Cyber IRP Risk Assessment | For internal use only
  </footer>
</div>
<!-- เพิ่ม Modal HTML สำหรับรายละเอียดความเสี่ยงสูง -->
<div class="modal fade" id="riskHighModal" tabindex="-1" aria-labelledby="riskHighModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="riskHighModalLabel">รายละเอียดความเสี่ยงสูง</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <div class="modal-body" id="riskHighModalBody">
        <!-- รายละเอียดจะแสดงตรงนี้ -->
      </div>
    </div>
  </div>
</div>
<div id="app" class="container mx-auto px-4 py-8">
  <!-- โค้ด JavaScript จะแทรก UI ในนี้ -->
</div>
<script>
// ---------------------------
// ส่วน: ค่าคงที่/การตั้งค่า
// ---------------------------
const QUESTIONS = <?php echo json_encode($questions); ?>;
const SCORE_MAP = { yes: 0, partial: 1, no: 2 };
const LABEL_MAP = { yes: "ใช่", partial: "ไม่แน่ใจ", no: "ไม่ใช่" };
const SECTION_ICONS = {
  "การจัดการข้อมูล": "🗂️",
  "การควบคุมสิทธิ์": "🔑",
  "การอัปเดต/ช่องโหว่": "🛡️",
  "การสำรองข้อมูล": "💾",
  "การตรวจจับเหตุการณ์": "🔎",
  "การตอบสนองเหตุการณ์": "🚨",
  "การป้องกันมัลแวร์": "🦠",
  "การบริหารจัดการ": "👔",
  "การปฏิบัติตามกฎหมาย": "📜",
  "โครงสร้างพื้นฐาน": "🌐",
  "Cloud/คลาวด์": "☁️"
};
let responses = [];
let current = 0;

// ---------------------------
// แสดง UI หลักเมื่อโหลดหน้า
// ---------------------------
document.addEventListener('DOMContentLoaded', function() {
  // ตรวจสอบว่ามี responses หรือไม่
  if (sessionStorage.getItem('cyberResponses')) {
    responses = JSON.parse(sessionStorage.getItem('cyberResponses'));
    renderResultsView();
  } else {
    renderQuestionView();
  }
});

// ---------------------------
// แสดงหน้าคำถาม
// ---------------------------
function renderQuestionView() {
  document.getElementById('app').innerHTML = `
    <div class="progress-container">
      <div class="progress-bar" style="width: 0%"></div>
    </div>
    <div id="questionContainer" class="fade-in"></div>
    <div class="btn-container">
      <button id="prevBtn" class="btn-secondary" onclick="prevQuestion()">ย้อนกลับ</button>
      <button id="nextBtn" class="btn-main" onclick="nextQuestion()">ต่อไป</button>
    </div>
  `;
  showQuestion(current);
}

// ---------------------------
// แสดงผลลัพธ์
// ---------------------------
function renderResultsView() {
  document.getElementById('app').innerHTML = `
    <div id="resultsContainer" class="fade-in">
      <h2 class="text-center text-2xl font-bold mb-8">ผลการประเมินความเสี่ยงด้านไซเบอร์</h2>
      <div id="mainBoxes" class="grid grid-cols-1 md:grid-cols-3 gap-6"></div>
    </div>
    <div id="mainModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
      <div class="bg-white max-w-4xl mx-auto my-10 p-8 rounded-xl shadow-xl">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold" id="modalTitle">รายละเอียด</h3>
          <button onclick="hideModal()" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div id="modalBody" class="overflow-y-auto max-h-[70vh]"></div>
      </div>
    </div>
  `;
  renderMainBoxes();
  
  // เพิ่มปุ่ม "เริ่มประเมินใหม่"
  document.getElementById('resultsContainer').insertAdjacentHTML('beforeend', `
    <div class="text-center mt-10">
      <button onclick="resetAssessment()" class="btn-secondary">เริ่มประเมินใหม่</button>
    </div>
  `);
}

// ---------------------------
// ฟังก์ชันแสดงกล่องข้อความแต่ละส่วน
// ---------------------------
function renderMainBoxes() {
  const boxes = [
    { id: "box-summary", icon: "📊", title: "สรุปผลประเมิน", desc: "ดูสรุปผลคะแนน จุดแข็ง และจุดที่ต้องปรับปรุง", func: "showSummaryBox" },
    { id: "box-high-risk", icon: "⚠️", title: "ข้อที่มีความเสี่ยงสูง", desc: "ดูรายการข้อที่มีความเสี่ยงสูงและแนวทางแก้ไข", func: "showHighRiskBox" },
    { id: "box-risk", icon: "📈", title: "Risk Assessment", desc: "ดูผลประเมินความเสี่ยงโดยละเอียด", func: "showRiskBox" },
    { id: "box-irp", icon: "🚨", title: "Incident Response Plan", desc: "ดูแผนตอบสนองเหตุการณ์ไซเบอร์", func: "showIRPBox" }
  ];
  
  let html = `<div class="grid grid-cols-1 md:grid-cols-2 gap-6">`;
  boxes.forEach(box => {
    html += `
      <div id="${box.id}" onclick="${box.func}()" class="bg-white rounded-xl shadow-lg p-6 cursor-pointer transform hover:scale-105 transition-all" style="border-left:6px solid #0ea5e9;">
        <div class="flex items-center mb-4">
          <span class="text-3xl mr-3">${box.icon}</span>
          <h3 class="text-xl font-bold text-blue-800">${box.title}</h3>
        </div>
        <p class="text-slate-700 mb-4">${box.desc}</p>
        <div class="text-right">
          <button class="inline-flex items-center text-blue-600 hover:text-blue-800">
            ดูรายละเอียด
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>
      </div>
    `;
  });
  html += `</div>`;
  document.getElementById('mainBoxes').innerHTML = html;
}

// ---------------------------
// ฟังก์ชันแสดงข้อมูลแต่ละกล่อง
// ---------------------------
function showSummaryBox() {
  document.getElementById('modalTitle').textContent = "สรุปผลประเมิน";
  document.getElementById('modalBody').innerHTML = `<div id="summaryResultBox" class="p-4"></div>`;
  renderSummaryOnly(responses, QUESTIONS);
  showModal();
}

function showHighRiskBox() {
  document.getElementById('modalTitle').textContent = "ข้อที่มีความเสี่ยงสูง";
  document.getElementById('modalBody').innerHTML = `<div id="highRiskResultBox" class="p-4"></div>`;
  renderHighRiskOnly(responses, QUESTIONS);
  showModal();
}

function showRiskBox() {
  document.getElementById('modalTitle').textContent = "การประเมินความเสี่ยง (Risk Assessment)";
  document.getElementById('modalBody').innerHTML = `<div id="riskResultBox" class="p-4"></div>`;
  renderRiskOnly(responses, QUESTIONS);
  showModal();
}

function showIRPBox() {
  document.getElementById('modalTitle').textContent = "แผนตอบสนองเหตุการณ์ (Incident Response Plan)";
  document.getElementById('modalBody').innerHTML = `<div id="irpResultBox" class="p-4"></div>`;
  renderIRPOnly(responses, QUESTIONS);
  showModal();
}

// ---------------------------
// ฟังก์ชัน render เฉพาะแต่ละส่วน
// ---------------------------
function renderSummaryOnly(responses, QUESTIONS) {
  let total = 0;
  let high = [], medium = [], low = [];
  let sectionScores = {};
  
  // จัดกลุ่มคำถามตามหมวดหมู่
  const sectionMap = {};
  QUESTIONS.forEach((q, i) => {
    if (!sectionMap[q.section]) sectionMap[q.section] = [];
    sectionMap[q.section].push(i);
  });

  for(let i=0; i<QUESTIONS.length; i++) {
    const ans = responses[i]?.choice;
    const score = SCORE_MAP[ans] || 0;
    const section = QUESTIONS[i].section;
    
    if(!sectionScores[section]) {
      sectionScores[section] = {total: 0, max: 0};
    }
    
    sectionScores[section].max += 2; // คะแนนเต็มคือ 2 ต่อข้อ
    if(score > 0) {
      total += score;
      sectionScores[section].total += score;
      if(score === 2) high.push(i);
      else if(score === 1) medium.push(i);
    }
  }
  
  // คำนวณคะแนนรวม
  const maxScore = QUESTIONS.length * 2;
  const percentage = Math.round((total / maxScore) * 100);
  const riskLevel = 
    percentage >= 75 ? {text: "ความเสี่ยงสูง", color: "#dc2626"} :
    percentage >= 40 ? {text: "ความเสี่ยงปานกลาง", color: "#d97706"} : 
                      {text: "ความเสี่ยงต่ำ", color: "#16a34a"};
  
  // จัดเรียงหมวดหมู่ตามระดับความเสี่ยง (มากไปน้อย)
  const sortedSections = Object.keys(sectionScores).sort((a, b) => {
    const scoreA = sectionScores[a].total / sectionScores[a].max;
    const scoreB = sectionScores[b].total / sectionScores[b].max;
    return scoreB - scoreA;
  });
  
  let html = `
    <div class="mb-8">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold">คะแนนรวม</h3>
          <p class="text-slate-600">ประเมิน ${responses.filter(r => r?.choice).length} จากทั้งหมด ${QUESTIONS.length} ข้อ</p>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" style="color: ${riskLevel.color}">${percentage}%</div>
          <div class="font-bold" style="color: ${riskLevel.color}">${riskLevel.text}</div>
        </div>
      </div>
      
      <div class="w-full bg-gray-200 rounded-full h-4 mt-2">
        <div class="h-4 rounded-full" style="width: ${percentage}%; background-color: ${riskLevel.color}"></div>
      </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
        <h4 class="font-bold text-red-700 mb-2">พบจุดเสี่ยงสูง ${high.length} จุด</h4>
        ${high.length > 0 ? 
          `<ul class="list-disc pl-5 text-red-700">
            ${high.slice(0, 5).map(i => `<li>${QUESTIONS[i].text}</li>`).join('')}
            ${high.length > 5 ? `<li>และอีก ${high.length - 5} รายการ...</li>` : ''}
          </ul>` : 
          '<p class="text-slate-600">ไม่พบจุดเสี่ยงสูง</p>'
        }
      </div>
      <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-md">
        <h4 class="font-bold text-amber-700 mb-2">พบจุดเสี่ยงปานกลาง ${medium.length} จุด</h4>
        ${medium.length > 0 ? 
          `<ul class="list-disc pl-5 text-amber-700">
            ${medium.slice(0, 5).map(i => `<li>${QUESTIONS[i].text}</li>`).join('')}
            ${medium.length > 5 ? `<li>และอีก ${medium.length - 5} รายการ...</li>` : ''}
          </ul>` : 
          '<p class="text-slate-600">ไม่พบจุดเสี่ยงปานกลาง</p>'
        }
      </div>
    </div>
    
    <h3 class="text-xl font-bold mb-4">ผลประเมินแยกตามหมวดหมู่</h3>
    <div class="grid grid-cols-1 gap-4">
      ${sortedSections.map(section => {
        const sectionScore = sectionScores[section];
        const sectionPercentage = Math.round((sectionScore.total / sectionScore.max) * 100);
        let barColor;
        if (sectionPercentage >= 75) barColor = "#dc2626";
        else if (sectionPercentage >= 40) barColor = "#d97706";
        else barColor = "#16a34a";
        
        return `
          <div class="bg-white p-4 rounded-md shadow-sm">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center">
                <span class="text-xl mr-2">${SECTION_ICONS[section] || '📋'}</span>
                <h4 class="font-bold">${section}</h4>
              </div>
              <div class="text-right">
                <span class="font-bold" style="color: ${barColor}">${sectionPercentage}%</span>
              </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full" style="width: ${sectionPercentage}%; background-color: ${barColor}"></div>
            </div>
          </div>
        `;
      }).join('')}
    </div>
  `;
  
  document.getElementById('summaryResultBox').innerHTML = html;
}

function renderHighRiskOnly(responses, QUESTIONS) {
  let highRiskIndexes = [];
  for(let i=0; i<QUESTIONS.length; i++) {
    const ans = responses[i]?.choice;
    const score = SCORE_MAP[ans] || 0;
    if(score === 2) highRiskIndexes.push(i);
  }
  
  if(highRiskIndexes.length === 0) {
    document.getElementById('highRiskResultBox').innerHTML = `
      <div class="bg-green-50 p-6 rounded-lg text-center">
        <div class="text-5xl mb-4">🎉</div>
        <h3 class="text-xl font-bold text-green-700 mb-2">ไม่พบข้อที่เสี่ยงสูง</h3>
        <p class="text-slate-600">ทุกรายการมีความเสี่ยงในระดับที่ยอมรับได้</p>
      </div>
    `;
    return;
  }
  
  let html = `
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-red-700">พบข้อที่มีความเสี่ยงสูง ${highRiskIndexes.length} รายการ</h3>
        <button onclick="printHighRiskReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"></path>
          </svg>
          พิมพ์รายงาน
        </button>
      </div>
      <p class="text-slate-600 mb-4">กรุณาจัดการแก้ไขรายการต่อไปนี้โดยด่วน</p>
    </div>
    
    <div class="space-y-6">
      ${highRiskIndexes.map((i, idx) => {
        const q = QUESTIONS[i];
        const ans = responses[i]?.choice;
        
        return `
          <div class="bg-white border-l-4 border-red-500 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-lg font-bold text-red-700">ข้อที่ ${i+1}: ${q.text}</h4>
              <span class="bg-red-100 text-red-800 text-sm font-bold px-3 py-1 rounded-full">เสี่ยงสูง</span>
            </div>
            
            <div class="space-y-3 mb-4">
              <div><b>คำตอบ:</b> <span class="text-blue-600">${LABEL_MAP[ans]||"-"}</span></div>
              <div><b>⚠️ ความเสี่ยง:</b> <span class="text-red-600">${q.risk}</span></div>
              <div><b>💡 คำแนะนำ:</b> <span class="text-blue-600">${q.fix}</span></div>
            </div>
            
            <div class="bg-slate-50 p-4 rounded-md">
              <div class="font-bold mb-2 text-slate-700">🔹 ขั้นตอนการแก้ไข:</div>
              <ol class="list-decimal pl-5 space-y-1 text-slate-700">
                ${q.steps.map(step=>`<li>${step}</li>`).join("")}
              </ol>
            </div>
            
            ${responses[i]?.comment ? `
              <div class="mt-4 bg-amber-50 p-3 rounded-md">
                <div class="font-bold text-amber-800">หมายเหตุ:</div>
                <div class="text-amber-700">${responses[i].comment}</div>
              </div>
            ` : ""}
          </div>
        `;
      }).join('')}
    </div>
  `;
  
  document.getElementById('highRiskResultBox').innerHTML = html;
}

function renderRiskOnly(responses, QUESTIONS) {
  const risk = getRiskData(responses, QUESTIONS);
  
  let html = `
    <div class="mb-6">
      <h3 class="text-xl font-bold mb-2">Risk Scenario Analysis</h3>
      <p class="text-slate-600 mb-4">การวิเคราะห์สถานการณ์ความเสี่ยงด้านไซเบอร์</p>
      
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="py-3 px-4 border-b text-left">Asset</th>
              <th class="py-3 px-4 border-b text-left">Threat</th>
              <th class="py-3 px-4 border-b text-left">Vulnerability</th>
              <th class="py-3 px-4 border-b text-left">Impact</th>
              <th class="py-3 px-4 border-b text-left">Risk Level</th>
            </tr>
          </thead>
          <tbody>
            ${risk.matrixRows.map(r=>`
              <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">${r.asset}</td>
                <td class="py-3 px-4">${r.threat}</td>
                <td class="py-3 px-4">${r.vuln}</td>
                <td class="py-3 px-4">${r.impact}</td>
                <td class="py-3 px-4">
                  <span class="px-2 py-1 rounded-full text-xs font-bold
                    ${r.riskLevel === 'High' ? 'bg-red-100 text-red-700' : 
                    r.riskLevel === 'Medium' ? 'bg-amber-100 text-amber-700' : 
                    'bg-green-100 text-green-700'}">
                    ${r.riskLevel}
                  </span>
                </td>
              </tr>
            `).join("")}
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="mb-6">
      <h3 class="text-xl font-bold mb-4">Risk Heatmap</h3>
      
      <div class="grid grid-cols-4 gap-px bg-gray-200 border border-gray-300">
        <div class="bg-white p-3 text-center font-bold">Impact ↓ / Likelihood →</div>
        <div class="bg-white p-3 text-center">Low (1)</div>
        <div class="bg-white p-3 text-center">Medium (2)</div>
        <div class="bg-white p-3 text-center">High (3)</div>
        
        ${[3, 2, 1].map(impact => `
          <div class="bg-white p-3 text-center font-bold">${impact === 3 ? 'High' : impact === 2 ? 'Medium' : 'Low'} (${impact})</div>
          ${[1, 2, 3].map(likelihood => {
            const cell = risk.heatmap[impact-1][likelihood-1];
            const count = cell.length;
            let cellClass = "bg-white";
            let textClass = "text-gray-500";
            
            if (count > 0) {
              if (impact * likelihood >= 7) { 
                cellClass = "bg-red-100"; 
                textClass = "text-red-800";
              } else if (impact * likelihood >= 4) { 
                cellClass = "bg-amber-100"; 
                textClass = "text-amber-800";
              } else { 
                cellClass = "bg-green-100"; 
                textClass = "text-green-800";
              }
            }
            
            return `
              <div class="${cellClass} p-3 text-center relative group cursor-pointer">
                <span class="${textClass} font-bold">${count || '-'}</span>
                ${ count > 0 ? `
                  <div class="hidden group-hover:block absolute z-10 w-64 p-3 bg-white border shadow-lg rounded-md -left-1/3 top-full">
                    <div class="font-bold mb-1">Impact: ${impact}, Likelihood: ${likelihood}</div>
                    <div class="text-sm mb-2">
                      Risk Level: <span class="${
                        impact * likelihood >= 7 ? "text-red-700 font-bold" : 
                        impact * likelihood >= 4 ? "text-amber-700 font-bold" : 
                        "text-green-700 font-bold"
                      }">${impact * likelihood >= 7 ? "High" : impact * likelihood >= 4 ? "Medium" : "Low"}</span>
                    </div>
                    <ul class="list-disc pl-4 text-sm">
                      ${risk.matrixRows.filter(r => r.impact === impact && r.likelihood === likelihood)
                        .map(r => `<li class="truncate" title="${r.asset}">${r.asset}</li>`)
                        .join("")}
                    </ul>
                  </div>
                ` : ''}
              </div>
            `;
          }).join("")}
        `).join("")}
      </div>
      
      <div class="flex items-center gap-4 mt-4 justify-center text-sm">
        <div class="flex items-center">
          <div class="w-4 h-4 rounded bg-red-100 border border-red-300 mr-1"></div>
          <span>High Risk</span>
        </div>
        <div class="flex items-center">
          <div class="w-4 h-4 rounded bg-amber-100 border border-amber-300 mr-1"></div>
          <span>Medium Risk</span>
        </div>
        <div class="flex items-center">
          <div class="w-4 h-4 rounded bg-green-100 border border-green-300 mr-1"></div>
          <span>Low Risk</span>
        </div>
      </div>
    </div>
    
    <div>
      <h3 class="text-xl font-bold mb-4">Risk Response Strategy</h3>
      
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="py-3 px-4 border-b text-left">Asset</th>
              <th class="py-3 px-4 border-b text-left">Risk Level</th>
              <th class="py-3 px-4 border-b text-left">Response Strategy</th>
            </tr>
          </thead>
          <tbody>
            ${risk.responseRows.map(r=>`
              <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">${r.asset}</td>
                <td class="py-3 px-4">
                  <span class="px-2 py-1 rounded-full text-xs font-bold
                    ${r.riskLevel === 'High' ? 'bg-red-100 text-red-700' : 
                    r.riskLevel === 'Medium' ? 'bg-amber-100 text-amber-700' : 
                    'bg-green-100 text-green-700'}">
                    ${r.riskLevel}
                  </span>
                </td>
                <td class="py-3 px-4">${r.response}</td>
              </tr>
            `).join("")}
          </tbody>
        </table>
      </div>
    </div>
  `;
  
  document.getElementById('riskResultBox').innerHTML = html;
}

function renderIRPOnly(responses, QUESTIONS) {
  const irp = getIRPData(responses, QUESTIONS);
  
  let html = `
    <div class="bg-blue-50 p-6 rounded-lg mb-6">
      <h3 class="text-xl font-bold text-blue-800 mb-2">Incident Classification</h3>
      <p class="text-slate-600 mb-2">จากการประเมินพบความเสี่ยงในเรื่อง:</p>
      <div class="flex flex-wrap gap-2 mt-2">
        ${irp.types.map(type => `
          <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
            ${type}
          </span>
        `).join('')}
      </div>
    </div>
    
    <div class="mb-6">
      <h3 class="text-xl font-bold mb-4">CIRT Team Roles</h3>
      <p class="text-slate-600 mb-4">Computer Incident Response Team ที่ต้องเตรียมพร้อม</p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        ${irp.cirtRoles.map(r=>`
          <div class="bg-white shadow rounded-lg p-4">
            <h4 class="font-bold text-lg text-blue-800">${r.role}</h4>
            <p class="text-slate-700 mb-2">${r.name}</p>
            <p class="text-slate-600 text-sm">${r.resp}</p>
          </div>
        `).join("")}
      </div>
    </div>
    
    <div>
      <h3 class="text-xl font-bold mb-4">Incident Response Phases</h3>
      
      <div class="relative">
        ${irp.phases.map((p, i)=>`
          <div class="mb-8 relative">
            ${i < irp.phases.length - 1 ? `
              <div class="absolute h-full w-1 bg-blue-200 left-4 top-4 z-0"></div>
            ` : ''}
            <div class="flex">
              <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center text-white font-bold z-10">
                ${i + 1}
              </div>
              <div class="ml-4 flex-1">
                <h4 class="text-lg font-bold text-blue-800 mb-2">${p.phase}</h4>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                  <table class="min-w-full">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="py-2 px-4 border-b text-left">Key Action</th>
                        <th class="py-2 px-4 border-b text-left">Responsible</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${p.actions.map(a=>`
                        <tr class="border-b hover:bg-gray-50">
                          <td class="py-3 px-4">${a.task}</td>
                          <td class="py-3 px-4">${a.who}</td>
                        </tr>
                      `).join("")}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        `).join("")}
      </div>
    </div>
  `;
  
  document.getElementById('irpResultBox').innerHTML = html;
}

// ---------------------------
// พิมพ์รายงานข้อที่เสี่ยงสูง
// ---------------------------
function printHighRiskReport() {
  let highRiskIndexes = [];
  for(let i=0; i<QUESTIONS.length; i++) {
    const ans = responses[i]?.choice;
    const score = SCORE_MAP[ans] || 0;
    if(score === 2) highRiskIndexes.push(i);
  }
  
  if(highRiskIndexes.length === 0) {
    alert("ไม่มีข้อที่เสี่ยงสูง");
    return;
  }
  
  let html = `<html><head>
    <title>ข้อที่เสี่ยงสูง - Cyber Risk Assessment</title>
    <style>
      body { font-family: 'Prompt', 'Sarabun', Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
      .header { text-align: center; margin-bottom: 30px; }
      .risk-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; border-left: 6px solid #dc2626; }
      .risk-title { font-size: 18px; font-weight: bold; color: #dc2626; margin-bottom: 10px; }
      .risk-detail { margin-bottom: 8px; }
      .risk-step { margin-left: 20px; color: #555; }
      .steps-container { background: #f8f9fa; padding: 15px; border-radius: 6px; margin: 10px 0; }
      .page-break { page-break-after: always; }
      @media print {
        body { font-size: 12pt; }
        .no-print { display: none; }
      }
    </style>
    </head>
    <body>
      <div class="header">
        <h1 style="color:#dc2626;margin-bottom:5px;">รายงานข้อที่เสี่ยงสูง</h1>
        <p>Cyber Risk Assessment Report</p>
        <p style="color:#666;">วันที่: ${new Date().toLocaleDateString('th-TH', {year: 'numeric', month: 'long', day: 'numeric'})}</p>
      </div>
      
      <p style="margin-bottom:20px;">พบข้อที่มีความเสี่ยงสูง <b>${highRiskIndexes.length}</b> รายการที่ต้องดำเนินการแก้ไขโดยด่วน</p>`;

  highRiskIndexes.forEach((i, idx) => {
    const q = QUESTIONS[i];
    const ans = responses[i]?.choice;
    
    // เพิ่ม page break ทุก 2 รายการ
    if (idx > 0 && idx % 2 === 0) {
      html += '<div class="page-break"></div>';
    }
    
    html += `
      <div class="risk-card">
        <div class="risk-title">ข้อที่ ${i+1}: ${q.text}</div>
        <div class="risk-detail"><b>หมวด:</b> ${q.section}</div>
        <div class="risk-detail"><b>คำตอบ:</b> <span style="color:#0ea5e9;">${LABEL_MAP[ans]||"-"}</span></div>
        <div class="risk-detail"><b>ระดับความเสี่ยง:</b> <span style="color:#dc2626;font-weight:bold;">เสี่ยงสูง</span></div>
        <div class="risk-detail"><b>⚠️ ความเสี่ยง:</b> ${q.risk}</div>
        <div class="risk-detail"><b>💡 คำแนะนำ:</b> ${q.fix}</div>
        <div class="steps-container">
          <div class="risk-detail"><b>🔹 ขั้นตอนการแก้ไข:</b></div>
          <ol class="risk-step">
            ${q.steps.map(step=>`<li>${step}</li>`).join("")}
          </ol>
        </div>
        ${responses[i]?.comment ? `<div class="risk-detail" style="margin-top:10px;"><b>หมายเหตุ:</b> ${responses[i].comment}</div>` : ""}
      </div>
    `;
  });

  html += `
      <div class="no-print" style="text-align:center;margin-top:30px;">
        <button onclick="window.print()" style="background:#0ea5e9;color:#fff;font-weight:bold;padding:10px 20px;border-radius:5px;border:none;cursor:pointer;">พิมพ์รายงาน</button>
      </div>
    </body></html>`;

  let win = window.open("", "_blank");
  win.document.write(html);
  win.document.close();
}

// ---------------------------
// Modal สำหรับแสดงผล
// ---------------------------
function showModal() {
  document.getElementById('mainModal').classList.remove('hidden');
  document.getElementById('mainModal').classList.add('flex');
}
function hideModal() {
  document.getElementById('mainModal').classList.add('hidden');
  document.getElementById('mainModal').classList.remove('flex');
}

// ---------------------------
// เริ่มประเมินใหม่
// ---------------------------
function resetAssessment() {
  if(confirm('คุณต้องการเริ่มการประเมินใหม่ใช่หรือไม่? ข้อมูลเดิมจะถูกลบ')) {
    sessionStorage.removeItem('cyberResponses');
    responses = [];
    current = 0;
    renderQuestionView();
  }
}

// ---------------------------
// ฟังก์ชันคำนวณความเสี่ยง (แบบเต็ม)
// ---------------------------
function getRiskData(responses, QUESTIONS) {
  let riskRows = [];
  let matrixRows = [];
  let responseRows = [];
  let heatmap = [[[],[],[]],[[],[],[]],[[],[],[]]]; // [impact-1][likelihood-1]
  
  for(let i=0;i<QUESTIONS.length;i++) {
    const ans = responses[i]?.choice;
    if(!ans) continue;
    
    const asset = QUESTIONS[i].text;
    const threat = QUESTIONS[i].risk;
    const vuln = QUESTIONS[i].fix;
    const consequence = QUESTIONS[i].risk;
    const likelihood = ans==="yes"?1 : ans==="partial"?2 : 3;
    let impact = /ข้อมูล|ระบบ|สูญหาย|โจมตี|รั่วไหล|ฟื้นฟู/.test(threat)?3:2;
    let riskScore = likelihood * impact;
    let riskLevel = riskScore>=7?"High":riskScore>=4?"Medium":"Low";
    let response = riskLevel==="High"?"Mitigate / Avoid":riskLevel==="Medium"?"Mitigate / Transfer":"Accept / Monitor";
    
    riskRows.push({asset, threat, vuln, consequence});
    matrixRows.push({asset, likelihood, impact, riskLevel});
    responseRows.push({asset, riskLevel, response});
    heatmap[impact-1][likelihood-1].push(riskLevel);
  }
  
  // เรียงลำดับตามระดับความเสี่ยง
  const riskOrder = { "High": 1, "Medium": 2, "Low": 3 };
  matrixRows.sort((a, b) => riskOrder[a.riskLevel] - riskOrder[b.riskLevel]);
  responseRows.sort((a, b) => riskOrder[a.riskLevel] - riskOrder[b.riskLevel]);
  
  return {riskRows, matrixRows, responseRows, heatmap};
}

// ---------------------------
// ฟังก์ชันคำนวณ IRP (แบบเต็ม)
// ---------------------------
function getIRPData(responses, QUESTIONS) {
  let types = [];
  if(responses[3]?.choice==="no") types.push("Account Compromise");
  if(responses[7]?.choice==="no") types.push("Vulnerability Exploitation");
  if(responses[11]?.choice==="no") types.push("Data Loss / Ransomware");
  if(responses[14]?.choice==="no"||responses[15]?.choice==="no") types.push("Malware Outbreak");
  if(responses[16]?.choice==="no"||responses[17]?.choice==="no") types.push("Incident Response Weakness");
  if(responses[18]?.choice==="no"||responses[22]?.choice==="no") types.push("Network Intrusion");
  if(responses[23]?.choice==="no"||responses[24]?.choice==="no") types.push("Cloud Security Issue");
  if(types.length===0) types.push("General Security Weakness");

  const cirtRoles = [
    {name:"นายสมชาย", role:"CIRT Lead", resp:"ควบคุมการตอบสนองเหตุการณ์และตัดสินใจหลัก"},
    {name:"นางสาวพรทิพย์", role:"Technical Lead", resp:"วิเคราะห์เทคนิคและแก้ไขปัญหา"},
    {name:"นายวิทยา", role:"Compliance Officer", resp:"ดูแลข้อกำหนด กฎหมาย และการรายงาน"},
    {name:"คุณอรทัย", role:"IT Support", resp:"สนับสนุนการกู้คืนระบบและประสานงาน"},
  ];

  const phases = [
    {
      phase:"Preparation",
      actions:[
        {task:"จัดทำนโยบายและแผน IRP", who:"CIRT Lead"},
        {task:"อบรมทีมงานและเตรียมเครื่องมือ", who:"Compliance Officer"},
        {task:"ทดสอบแผนและช่องทางติดต่อ", who:"CIRT Lead, IT Support"}
      ]
    },
    {
      phase:"Detection & Analysis",
      actions:[
        {task:"ตรวจจับเหตุการณ์ผิดปกติจาก Log/SIEM", who:"Technical Lead"},
        {task:"วิเคราะห์เหตุการณ์และประเมินผลกระทบ", who:"Technical Lead, CIRT Lead"},
        {task:"แจ้งเตือนทีมที่เกี่ยวข้อง", who:"CIRT Lead"}
      ]
    },
    {
      phase:"Containment & Recovery",
      actions:[
        {task:"จำกัดขอบเขตเหตุการณ์", who:"Technical Lead"},
        {task:"กู้คืนระบบ/ข้อมูลจากสำรอง", who:"IT Support"},
        {task:"ตรวจสอบความสมบูรณ์ของระบบ", who:"Technical Lead, IT Support"}
      ]
    },
    {
      phase:"Post-Incident Activities",
      actions:[
        {task:"สรุปบทเรียนและปรับปรุงแผน", who:"CIRT Lead, Compliance Officer"},
        {task:"รายงานต่อผู้บริหาร/หน่วยงานกำกับ", who:"Compliance Officer"},
        {task:"อบรมและสื่อสารกับบุคลากร", who:"CIRT Lead"}
      ]
    }
  ];
  
  return {types, cirtRoles, phases};
}

// ----------------------------
// ฟังก์ชันแสดงคำถาม (ส่วน Quiz)
// ----------------------------
function showQuestion(index) {
  if (index < 0) index = 0;
  if (index >= QUESTIONS.length) {
    finishQuiz();
    return;
  }
  
  current = index;
  const q = QUESTIONS[index];
  const resp = responses[index] || {};
  
  document.querySelector(".progress-bar").style.width = `${((index + 1) / QUESTIONS.length) * 100}%`;
  
  let html = `
    <div class="question-section">
      <div class="question-header">
        <div class="section-tag">${SECTION_ICONS[q.section] || '📋'} ${q.section}</div>
        <div class="question-num">ข้อที่ ${index + 1} จาก ${QUESTIONS.length}</div>
      </div>
      <div class="question-text">${q.text}</div>
      <div class="choices">
        <label class="choice-item ${resp.choice === 'yes' ? 'selected' : ''}">
          <input type="radio" name="q${index}" value="yes" ${resp.choice === 'yes' ? 'checked' : ''}>
          <span class="choice-text">ใช่</span>
          <span class="choice-desc">ดำเนินการแล้ว</span>
        </label>
        <label class="choice-item ${resp.choice === 'partial' ? 'selected' : ''}">
          <input type="radio" name="q${index}" value="partial" ${resp.choice === 'partial' ? 'checked' : ''}>
          <span class="choice-text">ไม่แน่ใจ</span>
          <span class="choice-desc">อยู่ระหว่างดำเนินการ</span>
        </label>
        <label class="choice-item ${resp.choice === 'no' ? 'selected' : ''}">
          <input type="radio" name="q${index}" value="no" ${resp.choice === 'no' ? 'checked' : ''}>
          <span class="choice-text">ไม่ใช่</span>
          <span class="choice-desc">ยังไม่ได้ดำเนินการ</span>
        </label>
      </div>
      <div class="comment-field">
        <label>หมายเหตุ (ถ้ามี):</label>
        <textarea id="comment">${resp.comment || ''}</textarea>
      </div>
    </div>
  `;
  
  document.getElementById("questionContainer").innerHTML = html;
  document.getElementById("prevBtn").style.visibility = index === 0 ? "hidden" : "visible";
  document.getElementById("nextBtn").textContent = index === QUESTIONS.length - 1 ? "เสร็จสิ้น" : "ถัดไป";
  
  // เพิ่ม Event listener สำหรับการเลือกคำตอบ
  document.querySelectorAll('.choice-item').forEach(item => {
    item.addEventListener('click', function() {
      document.querySelectorAll('.choice-item').forEach(el => el.classList.remove('selected'));
      this.classList.add('selected');
    });
  });
}

function prevQuestion() {
  saveCurrentResponse();
  showQuestion(current - 1);
}

function nextQuestion() {
  if (!saveCurrentResponse()) {
    alert("กรุณาเลือกคำตอบ");
    return;
  }
  showQuestion(current + 1);
}

function saveCurrentResponse() {
  const selected = document.querySelector(`input[name="q${current}"]:checked`);
  if (!selected) return false;
  
  const comment = document.getElementById("comment").value;
  responses[current] = { choice: selected.value, comment };
  sessionStorage.setItem('cyberResponses', JSON.stringify(responses));
  return true;
}

function finishQuiz() {
  sessionStorage.setItem('cyberResponses', JSON.stringify(responses));
  renderResultsView();
}
</script>
</body>
</html>