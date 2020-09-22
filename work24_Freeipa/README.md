Linux Administrator 2020

################################
#Домашнее задание 24 Freeeipa  #
################################
         
         

<details>
<summary><code>1. Установить FreeIPA </code></summary>

Примчение:

Предварительно поправил "hostname" машины на 

```
freeipa.otus.lan

```
А так же занес соответсвующие записи в /etc/hosts

```
192.168.100.160 freeipa.otus.lan freeipa

```

Тут за нас все сделает ansible, собстно отрывок таска, а так достаточно установить два пакета
<code>ipa-server и ipa-server-dns</code>

```
 - name: install freeipa
    yum:
     name:
      - net-tools
      - vim
      - wget
      - mc
      - ipa-server
      - bind
      - bind-dyndb-ldap
      - ipa-server-dns

```
Настройку тоже за нас делает Ansible, но можно и в ручную в интеративном режиме <code>ipa-server-install</code>


в итоге должно получиться такой успешный вывод

```


```

</details>



<details>

<summary><code>Написать Ansible playbook для конфигурации клиента</code></summary>

```

```

</details>


<details>
<summary><code>3*. Настроить аутентификацию по SSH-ключам</code></summary>

```


```

</details>




<details>
<summary><code>4**. Firewall должен быть включен на сервере и на клиенте.</code></summary>

```


```

</details>




