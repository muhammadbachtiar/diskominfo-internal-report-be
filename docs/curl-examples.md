Examples (assumes `Authorization: Bearer <token>` header)

- Create draft
  curl -X POST http://localhost:8000/api/v1/reports \
    -H 'Authorization: Bearer TOKEN' \
    -H 'Content-Type: application/json' \
    -d '{"title":"Inspeksi Jaringan","lat":-3.7,"lng":103.8,"accuracy":10}'

- Update draft
  curl -X PATCH http://localhost:8000/api/v1/reports/{id} \
    -H 'Authorization: Bearer TOKEN' \
    -H 'Content-Type: application/json' \
    -d '{"title":"Judul Baru"}'

- Presign evidence
  curl -X POST http://localhost:8000/api/v1/reports/{id}/evidences/presign \
    -H 'Authorization: Bearer TOKEN' \
    -H 'Content-Type: application/json' \
    -d '{"mime":"image/jpeg"}'

- Finalize evidence
  curl -X POST http://localhost:8000/api/v1/reports/{id}/evidences/finalize \
    -H 'Authorization: Bearer TOKEN' \
    -H 'Content-Type: application/json' \
    -d '{"object_key":"evidence/...","original_name":"foto.jpg","mime":"image/jpeg","size":12345}'

- Delete evidence
  curl -X DELETE http://localhost:8000/api/v1/reports/{id}/evidences/{evidenceId} \
    -H 'Authorization: Bearer TOKEN'

- Submit
  curl -X POST http://localhost:8000/api/v1/reports/{id}/submit -H 'Authorization: Bearer TOKEN'

- Review (approve)
  curl -X POST http://localhost:8000/api/v1/reports/{id}/review \
    -H 'Authorization: Bearer TOKEN' \
    -H 'Content-Type: application/json' \
    -d '{"decision":"approve"}'

- Delete report (draft/revision)
  curl -X DELETE http://localhost:8000/api/v1/reports/{id} \
    -H 'Authorization: Bearer TOKEN'

- Notifications
  curl -H 'Authorization: Bearer TOKEN' http://localhost:8000/api/v1/notifications
  curl -X POST -H 'Authorization: Bearer TOKEN' http://localhost:8000/api/v1/notifications/read-all
  curl -X POST -H 'Authorization: Bearer TOKEN' http://localhost:8000/api/v1/notifications/{notificationId}/read
